<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks method calls in a loop that run at most once per run of the loop with LEAVES_LOOP:
 * the call is in a statement of its own, or in a return, and every way on from it in the
 * same iteration reaches return, throw, or a break out of the only loop around it.
 *
 * It stays on the safe side: a continue, break or goto on the way, a call inside try,
 * in a closure or in the condition of an if, and loops in closures are not marked.
 */
final class LoopExitVisitor extends NodeVisitorAbstract
{
    public const LEAVES_LOOP = 'magentoPerfLeavesLoop';

    private const STATEMENT = 'magentoPerfStatementLeavesLoop';

    /** @var list<bool> whether calls met now are in a marked statement, one entry per open statement or function */
    private array $open = [];

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->open = [];

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if (($node instanceof Stmt\ClassMethod || $node instanceof Stmt\Function_) && $node->stmts !== null) {
            $this->walk($node->stmts, 0, false, null);
        }
        if ($node instanceof Node\FunctionLike) {
            $this->open[] = false;
        } elseif ($node instanceof Stmt\Expression || $node instanceof Stmt\Return_) {
            $this->open[] = $node->getAttribute(self::STATEMENT) === true;
        } elseif ($node instanceof Expr\MethodCall && end($this->open) === true) {
            $node->setAttribute(self::LEAVES_LOOP, true);
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node instanceof Node\FunctionLike || $node instanceof Stmt\Expression || $node instanceof Stmt\Return_) {
            array_pop($this->open);
        }

        return null;
    }

    /**
     * @param Node\Stmt[] $stmts
     * @param bool $leavesAtEnd whether reaching the end of the list leaves the loop
     * @param ?bool $breakLeaves null when break targets a loop; inside a switch, whether what follows the switch leaves the loop
     */
    private function walk(array $stmts, int $loops, bool $leavesAtEnd, ?bool $breakLeaves): void
    {
        // Whether what follows each statement leaves the loop, worked out once from the end of the list.
        $after = [];
        $next = $leavesAtEnd;
        foreach (array_reverse($stmts, true) as $i => $stmt) {
            $after[$i] = $next;
            $next = $this->leaves($stmt, $next, $loops, $breakLeaves);
        }

        foreach ($stmts as $i => $stmt) {
            $leaves = $after[$i];
            if ($stmt instanceof Stmt\Return_ || ($stmt instanceof Stmt\Expression && $leaves)) {
                $stmt->setAttribute(self::STATEMENT, true);
            }
            if ($stmt instanceof Stmt\If_) {
                $this->walk($stmt->stmts, $loops, $leaves, $breakLeaves);
                foreach ($stmt->elseifs as $elseif) {
                    $this->walk($elseif->stmts, $loops, $leaves, $breakLeaves);
                }
                if ($stmt->else !== null) {
                    $this->walk($stmt->else->stmts, $loops, $leaves, $breakLeaves);
                }
            } elseif ($stmt instanceof Stmt\Block) {
                $this->walk($stmt->stmts, $loops, $leaves, $breakLeaves);
            } elseif ($stmt instanceof Stmt\Switch_) {
                // A case without break falls through to the next one; only the end of the last one ends the switch.
                $last = array_key_last($stmt->cases);
                foreach ($stmt->cases as $k => $case) {
                    $this->walk($case->stmts, $loops, $k === $last && $leaves, $leaves);
                }
            } elseif ($stmt instanceof Stmt\Foreach_ || $stmt instanceof Stmt\For_ || $stmt instanceof Stmt\While_ || $stmt instanceof Stmt\Do_) {
                $this->walk($stmt->stmts, $loops + 1, false, null);
            }
        }
    }

    /**
     * Whether running the statement, then what follows it, leaves the loop.
     *
     * @param bool $after whether what follows the statement leaves the loop
     */
    private function leaves(Node\Stmt $stmt, bool $after, int $loops, ?bool $breakLeaves): bool
    {
        if ($stmt instanceof Stmt\Return_ || self::isThrow($stmt)) {
            return true;
        }
        if ($stmt instanceof Stmt\Break_) {
            return $stmt->num === null && ($breakLeaves ?? $loops === 1);
        }
        $jump = (new NodeFinder())->findFirst(
            $stmt,
            static fn (Node $node): bool => $node instanceof Stmt\Continue_ || $node instanceof Stmt\Break_ || $node instanceof Stmt\Goto_,
        );

        return $jump === null && $after;
    }

    /** "throw" is a statement in php-parser 4 and an expression statement in php-parser 5. */
    private static function isThrow(Node\Stmt $stmt): bool
    {
        return $stmt->getType() === 'Stmt_Throw'
            || ($stmt instanceof Stmt\Expression && $stmt->expr instanceof Expr\Throw_);
    }
}
