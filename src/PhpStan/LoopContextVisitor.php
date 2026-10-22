<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks every node that runs once per iteration with the IN_LOOP attribute:
 * the bodies of foreach/for/while/do, and the bodies of closures passed straight
 * to array_map(), array_walk() or a collection's walk().
 *
 * The iterated expression of foreach and the init of for are not marked:
 * they run once.
 */
final class LoopContextVisitor extends NodeVisitorAbstract
{
    public const IN_LOOP = 'magentoPerfInLoop';

    private const BODY_ROOT = 'magentoPerfLoopBodyRoot';

    /** Callback argument position per iterating function. */
    private const ITERATING_FUNCTIONS = ['array_map' => 0, 'array_walk' => 1];

    private const ITERATING_METHODS = ['walk' => 0];

    private int $depth = 0;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->depth = 0;

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node->getAttribute(self::BODY_ROOT) === true) {
            ++$this->depth;
        }
        if ($this->depth > 0) {
            $node->setAttribute(self::IN_LOOP, true);
        }

        if ($node instanceof Stmt\Foreach_
            || $node instanceof Stmt\For_
            || $node instanceof Stmt\While_
            || $node instanceof Stmt\Do_
        ) {
            $this->markBodyRoots($node->stmts);
        } elseif ($node instanceof Expr\FuncCall && $node->name instanceof Node\Name) {
            $position = self::ITERATING_FUNCTIONS[$node->name->toLowerString()] ?? null;
            if ($position !== null) {
                $this->markCallback($node->getArgs(), $position);
            }
        } elseif ($node instanceof Expr\MethodCall && $node->name instanceof Node\Identifier) {
            $position = self::ITERATING_METHODS[$node->name->toLowerString()] ?? null;
            if ($position !== null) {
                $this->markCallback($node->getArgs(), $position);
            }
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node->getAttribute(self::BODY_ROOT) === true) {
            --$this->depth;
        }

        return null;
    }

    /**
     * @param Node[] $nodes
     */
    private function markBodyRoots(array $nodes): void
    {
        foreach ($nodes as $node) {
            $node->setAttribute(self::BODY_ROOT, true);
        }
    }

    /**
     * @param Node\Arg[] $args
     */
    private function markCallback(array $args, int $position): void
    {
        $callback = $args[$position]->value ?? null;
        if ($callback instanceof Expr\Closure) {
            $this->markBodyRoots($callback->stmts);
        } elseif ($callback instanceof Expr\ArrowFunction) {
            $callback->expr->setAttribute(self::BODY_ROOT, true);
        }
    }
}
