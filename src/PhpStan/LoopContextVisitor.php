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
 * Bodies of foreach and of those closures, which run once per item rather than until a
 * condition changes, also get IN_ITEM_LOOP.
 *
 * The iterated expression of foreach and the init of for are not marked:
 * they run once. Neither is the body of a foreach over array_chunk(): it runs once
 * per batch, which is the fix for a query per item.
 *
 * Within one iteration it also marks load-type calls (load, getItems, getSize,
 * getFirstItem, count) with CREATED_IN_LOOP when their collection was created by
 * create() in that same iteration: in the call chain itself, or through a variable
 * assigned inside the loop body. One collection gets one mark: its first loading call.
 */
final class LoopContextVisitor extends NodeVisitorAbstract
{
    public const IN_LOOP = 'magentoPerfInLoop';

    public const IN_ITEM_LOOP = 'magentoPerfInItemLoop';

    public const CREATED_IN_LOOP = 'magentoPerfCreatedInLoop';

    /** On a method call whose receiver is the value variable of a foreach: the expression that foreach iterates. */
    public const ITERATED_EXPRESSION = 'magentoPerfIteratedExpression';

    private const BODY_ROOT = 'magentoPerfLoopBodyRoot';
    private const ITEM_BODY_ROOT = 'magentoPerfItemLoopBodyRoot';
    private const ITERATION_SCOPE = 'magentoPerfIterationScope';
    private const RUNS_ONCE = 'magentoPerfRunsOnce';
    private const COVERED_BY_CHAIN = 'magentoPerfCoveredByChain';

    private const LOADING_METHODS = ['load', 'getitems', 'getsize', 'getfirstitem', 'count'];

    /** Callback argument position per iterating function. */
    private const ITERATING_FUNCTIONS = ['array_map' => 0, 'array_walk' => 1];

    private const ITERATING_METHODS = ['walk' => 0];

    private int $depth = 0;

    private int $itemDepth = 0;

    /** Nesting inside the iterated expression of foreach or the init of for. */
    private int $once = 0;

    /** @var list<array<string, bool>> variables assigned from create() => not loaded yet, one set per enclosing iteration */
    private array $created = [];

    /** @var list<Stmt\Foreach_> enclosing foreach statements, innermost last */
    private array $foreaches = [];

    /** @var list<array<string, true>> variables holding array_chunk(), one set per function */
    private array $batches = [[]];

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->depth = 0;
        $this->itemDepth = 0;
        $this->once = 0;
        $this->created = [];
        $this->batches = [[]];
        $this->foreaches = [];

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node->getAttribute(self::BODY_ROOT) === true) {
            ++$this->depth;
        }
        if ($node->getAttribute(self::ITEM_BODY_ROOT) === true) {
            ++$this->itemDepth;
        }
        if ($this->depth > 0) {
            $node->setAttribute(self::IN_LOOP, true);
        }
        if ($this->itemDepth > 0) {
            $node->setAttribute(self::IN_ITEM_LOOP, true);
        }
        if ($node->getAttribute(self::RUNS_ONCE) === true) {
            ++$this->once;
        }
        if ($node->getAttribute(self::ITERATION_SCOPE) === true) {
            $this->created[] = [];
        }
        $this->trackCreatedCollections($node);
        $this->markIteratedExpression($node);

        if ($node instanceof Node\FunctionLike && !$node instanceof Expr\ArrowFunction) {
            $this->batches[] = [];
        }
        if ($node instanceof Expr\Assign && $node->var instanceof Expr\Variable && is_string($node->var->name)) {
            if (self::isChunk($node->expr)) {
                $this->batches[count($this->batches) - 1][$node->var->name] = true;
            } else {
                unset($this->batches[count($this->batches) - 1][$node->var->name]);
            }
        }
        if ($node instanceof Stmt\Foreach_ && $this->iteratesBatches($node->expr)) {
            return null;
        }

        if ($node instanceof Stmt\Foreach_
            || $node instanceof Stmt\For_
            || $node instanceof Stmt\While_
            || $node instanceof Stmt\Do_
        ) {
            $node->setAttribute(self::ITERATION_SCOPE, true);
            $this->created[] = [];
            $this->markBodyRoots($node->stmts, $node instanceof Stmt\Foreach_);
            if ($node instanceof Stmt\Foreach_) {
                $this->foreaches[] = $node;
                $node->expr->setAttribute(self::RUNS_ONCE, true);
            } elseif ($node instanceof Stmt\For_) {
                foreach ($node->init as $init) {
                    $init->setAttribute(self::RUNS_ONCE, true);
                }
            }
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
        if ($node->getAttribute(self::ITEM_BODY_ROOT) === true) {
            --$this->itemDepth;
        }
        if ($node->getAttribute(self::RUNS_ONCE) === true) {
            --$this->once;
        }
        if ($node->getAttribute(self::ITERATION_SCOPE) === true) {
            array_pop($this->created);
        }
        if ($node instanceof Stmt\Foreach_ && end($this->foreaches) === $node) {
            array_pop($this->foreaches);
        }
        if ($node instanceof Node\FunctionLike && !$node instanceof Expr\ArrowFunction) {
            array_pop($this->batches);
        }

        return null;
    }

    /**
     * @param Node[] $nodes
     */
    private function markBodyRoots(array $nodes, bool $perItem): void
    {
        foreach ($nodes as $node) {
            $node->setAttribute(self::BODY_ROOT, true);
            $node->setAttribute(self::ITEM_BODY_ROOT, $perItem);
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
            $this->markBodyRoots($callback->stmts, true);
        } elseif ($callback instanceof Expr\ArrowFunction) {
            $callback->setAttribute(self::ITERATION_SCOPE, true);
            $this->markBodyRoots([$callback->expr], true);
        }
    }

    private function trackCreatedCollections(Node $node): void
    {
        // The iteration scope opens before the iterated expression of foreach and the
        // init of for are visited. Outside any loop body they run once; conditions
        // and the step of for run on every iteration and still count.
        if ($this->created === [] || ($this->depth === 0 && $this->once > 0)) {
            return;
        }
        if ($node instanceof Expr\Assign && $node->var instanceof Expr\Variable && is_string($node->var->name)
            && $this->chainCallsCreate($node->expr)
        ) {
            $this->created[count($this->created) - 1][$node->var->name] = !self::chainLoads($node->expr);

            return;
        }
        if (!$node instanceof Expr\MethodCall || !$node->name instanceof Node\Identifier
            || !in_array($node->name->toLowerString(), self::LOADING_METHODS, true)
            || $node->getAttribute(self::COVERED_BY_CHAIN) === true
        ) {
            return;
        }
        $root = $node->var;
        while ($root instanceof Expr\MethodCall) {
            $root = $root->var;
        }
        $createdVariable = false;
        if ($root instanceof Expr\Variable && is_string($root->name)) {
            foreach ($this->created as $i => $scope) {
                if (($scope[$root->name] ?? false) === true) {
                    $createdVariable = true;
                    $this->created[$i][$root->name] = false;
                }
            }
        }
        if ($createdVariable || $this->chainCallsCreate($node->var)) {
            $node->setAttribute(self::CREATED_IN_LOOP, true);
            for ($inner = $node->var; $inner instanceof Expr\MethodCall; $inner = $inner->var) {
                $inner->setAttribute(self::COVERED_BY_CHAIN, true);
            }
        }
    }

    private function markIteratedExpression(Node $node): void
    {
        if (!$node instanceof Expr\MethodCall || !$node->var instanceof Expr\Variable) {
            return;
        }
        foreach (array_reverse($this->foreaches) as $foreach) {
            if ($foreach->valueVar instanceof Expr\Variable && $foreach->valueVar->name === $node->var->name) {
                $node->setAttribute(self::ITERATED_EXPRESSION, $foreach->expr);

                return;
            }
        }
    }

    private static function chainLoads(Expr $expr): bool
    {
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            if ($expr->name instanceof Node\Identifier && in_array($expr->name->toLowerString(), self::LOADING_METHODS, true)) {
                return true;
            }
        }

        return false;
    }

    private static function isChunk(Expr $expr): bool
    {
        return $expr instanceof Expr\FuncCall && $expr->name instanceof Node\Name && $expr->name->toLowerString() === 'array_chunk';
    }

    private function iteratesBatches(Expr $expr): bool
    {
        return self::isChunk($expr)
            || ($expr instanceof Expr\Variable && is_string($expr->name) && isset($this->batches[count($this->batches) - 1][$expr->name]));
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
