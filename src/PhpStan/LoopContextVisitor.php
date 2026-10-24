<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

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
 *
 * Within one iteration it also marks load-type calls (load, getItems, getSize,
 * getFirstItem, count) with CREATED_IN_LOOP when their collection was created by
 * create() in that same iteration: in the call chain itself, or through a variable
 * assigned inside the loop body.
 */
final class LoopContextVisitor extends NodeVisitorAbstract
{
    public const IN_LOOP = 'magentoPerfInLoop';

    public const CREATED_IN_LOOP = 'magentoPerfCreatedInLoop';

    private const BODY_ROOT = 'magentoPerfLoopBodyRoot';
    private const ITERATION_SCOPE = 'magentoPerfIterationScope';

    private const LOADING_METHODS = ['load', 'getitems', 'getsize', 'getfirstitem', 'count'];

    /** Callback argument position per iterating function. */
    private const ITERATING_FUNCTIONS = ['array_map' => 0, 'array_walk' => 1];

    private const ITERATING_METHODS = ['walk' => 0];

    private int $depth = 0;

    /** @var list<array<string, true>> variables assigned from create(), one set per enclosing iteration */
    private array $created = [];

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->depth = 0;
        $this->created = [];

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
        if ($node->getAttribute(self::ITERATION_SCOPE) === true) {
            $this->created[] = [];
        }
        $this->trackCreatedCollections($node);

        if ($node instanceof Stmt\Foreach_
            || $node instanceof Stmt\For_
            || $node instanceof Stmt\While_
            || $node instanceof Stmt\Do_
        ) {
            $node->setAttribute(self::ITERATION_SCOPE, true);
            $this->created[] = [];
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
        if ($node->getAttribute(self::ITERATION_SCOPE) === true) {
            array_pop($this->created);
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
            $callback->setAttribute(self::ITERATION_SCOPE, true);
            $this->markBodyRoots($callback->stmts);
        } elseif ($callback instanceof Expr\ArrowFunction) {
            $callback->setAttribute(self::ITERATION_SCOPE, true);
            $callback->expr->setAttribute(self::BODY_ROOT, true);
        }
    }

    private function trackCreatedCollections(Node $node): void
    {
        if ($this->created === []) {
            return;
        }
        if ($node instanceof Expr\Assign && $node->var instanceof Expr\Variable && is_string($node->var->name)
            && $this->chainCallsCreate($node->expr)
        ) {
            $this->created[count($this->created) - 1][$node->var->name] = true;

            return;
        }
        if (!$node instanceof Expr\MethodCall || !$node->name instanceof Node\Identifier
            || !in_array($node->name->toLowerString(), self::LOADING_METHODS, true)
        ) {
            return;
        }
        $root = $node->var;
        while ($root instanceof Expr\MethodCall) {
            $root = $root->var;
        }
        $createdVariable = false;
        if ($root instanceof Expr\Variable && is_string($root->name)) {
            foreach ($this->created as $scope) {
                $createdVariable = $createdVariable || isset($scope[$root->name]);
            }
        }
        if ($createdVariable || $this->chainCallsCreate($node->var)) {
            $node->setAttribute(self::CREATED_IN_LOOP, true);
        }
    }

    private function chainCallsCreate(Expr $expr): bool
    {
        while ($expr instanceof Expr\MethodCall) {
            if ($expr->name instanceof Node\Identifier && $expr->name->toLowerString() === 'create') {
                return true;
            }
            $expr = $expr->var;
        }

        return false;
    }
}
