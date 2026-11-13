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
 * It stays on the safe side: a continue, break or goto on the way, a call in a closure or in the
 * condition of an if, and loops in closures are not marked. A try outside every loop is walked: its
 * catch runs once the loop is left. In a try inside the loop, a call in a catch or in the finally is
 * marked when the way on leaves the loop. The try itself is walked only when every catch leaves the
 * loop too: an exception, which Magento also throws for "not found", must not go on with the next
 * iteration and run the call again.
 *
 * Calls are marked when their function is entered, before any visitor reaches them,
 * so the order in which visitors are registered does not matter.
 */
final class LoopExitVisitor extends NodeVisitorAbstract
{
    public const LEAVES_LOOP = 'magentoPerfLeavesLoop';

    public function enterNode(Node $node): ?Node
    {
        if (($node instanceof Stmt\ClassMethod || $node instanceof Stmt\Function_) && $node->stmts !== null) {
            $this->walk($node->stmts, 0, false, null);
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
        $after = $this->afterEach($stmts, $leavesAtEnd, $loops, $breakLeaves);

        foreach ($stmts as $i => $stmt) {
            $leaves = $after[$i];
            // Outside any loop of this function, a return leaves only the function: a method of a class
            // declared in a loop body is still loop context.
            if ($loops > 0 && ($stmt instanceof Stmt\Return_ || ($stmt instanceof Stmt\Expression && $leaves)) && $stmt->expr !== null) {
                self::markCalls($stmt->expr);
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
            } elseif ($stmt instanceof Stmt\TryCatch && $loops === 0) {
                // Outside any loop, an exception reaches its catch only after leaving the loops inside the try,
                // so they are walked as usual.
                $this->walk($stmt->stmts, 0, false, null);
                foreach ($stmt->catches as $catch) {
                    $this->walk($catch->stmts, 0, false, null);
                }
                if ($stmt->finally !== null) {
                    $this->walk($stmt->finally->stmts, 0, false, null);
                }
            } elseif ($stmt instanceof Stmt\TryCatch) {
                // Inside a loop, a catch goes on with what follows the try. The try is walked only when no
                // catch can go on with the next iteration; then a throw in it, caught there, leaves too.
                $catchesLeave = true;
                foreach ($stmt->catches as $catch) {
                    $this->walk($catch->stmts, $loops, $leaves, $breakLeaves);
                    $catchesLeave = $catchesLeave && $this->listLeaves($catch->stmts, $leaves, $loops, $breakLeaves);
                }
                if ($catchesLeave) {
                    $this->walk($stmt->stmts, $loops, $leaves, $breakLeaves);
                }
                // The finally also runs after a continue or break in the try or a catch.
                if ($stmt->finally !== null) {
                    $this->walk($stmt->finally->stmts, $loops, $this->leaves($stmt, $leaves, $loops, $breakLeaves), $breakLeaves);
                }
            }
        }
    }

    /**
     * Whether what follows each statement leaves the loop, worked out from the end of the list.
     *
     * @param Node\Stmt[] $stmts
     * @return array<bool> keyed like $stmts
     */
    private function afterEach(array $stmts, bool $leavesAtEnd, int $loops, ?bool $breakLeaves): array
    {
        $after = [];
        $next = $leavesAtEnd;
        foreach (array_reverse($stmts, true) as $i => $stmt) {
            $after[$i] = $next;
            $next = $this->leaves($stmt, $next, $loops, $breakLeaves);
        }

        return $after;
    }

    /**
     * Whether running the whole list, then what follows it, leaves the loop.
     *
     * @param Node\Stmt[] $stmts
     */
    private function listLeaves(array $stmts, bool $leavesAtEnd, int $loops, ?bool $breakLeaves): bool
    {
        $first = array_key_first($stmts);
        if ($first === null) {
            return $leavesAtEnd;
        }

        return $this->leaves($stmts[$first], $this->afterEach($stmts, $leavesAtEnd, $loops, $breakLeaves)[$first], $loops, $breakLeaves);
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
        // A plain break inside a switch leaves the switch, or a loop inside it, and goes on after the switch.
        $jump = (new NodeFinder())->findFirst(
            $stmt,
            static fn (Node $node): bool => $node instanceof Stmt\Continue_ || $node instanceof Stmt\Goto_
                || ($node instanceof Stmt\Break_ && !($stmt instanceof Stmt\Switch_ && $node->num === null)),
        );

        return $jump === null && $after;
    }

    /**
     * Marks the method calls that run as part of the expression; closures and classes declared in it run later.
     */
    private static function markCalls(Node $node): void
    {
        if ($node instanceof Expr\Closure || $node instanceof Expr\ArrowFunction || $node instanceof Stmt\ClassLike) {
            return;
        }
        if ($node instanceof Expr\MethodCall) {
            $node->setAttribute(self::LEAVES_LOOP, true);
        }
        foreach ($node->getSubNodeNames() as $name) {
            $sub = $node->$name;
            foreach (is_array($sub) ? $sub : [$sub] as $child) {
                if ($child instanceof Node) {
                    self::markCalls($child);
                }
            }
        }
    }

    /** "throw" is a statement in php-parser 4 and an expression statement in php-parser 5. */
    private static function isThrow(Node\Stmt $stmt): bool
    {
        return $stmt->getType() === 'Stmt_Throw'
            || ($stmt instanceof Stmt\Expression && $stmt->expr instanceof Expr\Throw_);
    }
}
