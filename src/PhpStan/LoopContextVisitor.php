<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
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
 *
 * A method call inside foreach/for/while/do, or inside a callback that array_map() and the like call for
 * each item, also gets LOOP_WRITES: the variables that the enclosing loops and callbacks, or a closure
 * between them and the call, may change, so an argument made only of others has
 * the same value in every iteration. The key "*" stands for any variable: a loop that changes variables
 * it does not name, or one in a function that takes references, which may change a variable anywhere.
 */
final class LoopContextVisitor extends NodeVisitorAbstract
{
    public const IN_LOOP = 'magentoPerfInLoop';

    public const IN_ITEM_LOOP = 'magentoPerfInItemLoop';

    public const CREATED_IN_LOOP = 'magentoPerfCreatedInLoop';

    /** On a method call in a loop statement: array<string, true> of the variable names the enclosing loops may change. */
    public const LOOP_WRITES = 'magentoPerfLoopWrites';

    /** On a method call whose receiver is the value variable of a foreach: the expression that foreach iterates. */
    public const ITERATED_EXPRESSION = 'magentoPerfIteratedExpression';

    private const BODY_ROOT = 'magentoPerfLoopBodyRoot';
    private const ITEM_BODY_ROOT = 'magentoPerfItemLoopBodyRoot';
    private const ITERATION_SCOPE = 'magentoPerfIterationScope';
    private const RUNS_ONCE = 'magentoPerfRunsOnce';
    private const COVERED_BY_CHAIN = 'magentoPerfCoveredByChain';
    private const WRITES_PUSHED = 'magentoPerfWritesPushed';

    private const LOADING_METHODS = ['load', 'getitems', 'getsize', 'getfirstitem', 'count'];

    /**
     * Methods that limit the rows of a query => position of their row count. A limit in a loop
     * that is not over items is paging: one query per page is the fix, not the problem.
     */
    private const PAGING_METHODS = ['setpagesize' => 0, 'limit' => 0, 'setpage' => 1, 'limitpage' => 1];

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

    /** @var list<array<string, true>> variables the enclosing loops and closures may change, merged from the outermost in */
    private array $writes = [];

    /** @var list<bool> whether each enclosing function takes or makes a reference */
    private array $references = [false];

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
        $this->writes = [];
        $this->references = [false];

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
            $this->references[] = self::takesReferences($node);
        }
        // A callback that array_map() and the like call per item changes its parameters like a loop does.
        if (self::isLoop($node) || (($this->writes !== [] || $node->getAttribute(self::ITERATION_SCOPE) === true)
            && ($node instanceof Expr\Closure || $node instanceof Expr\ArrowFunction))
        ) {
            $written = self::writtenVariables($node);
            if (end($this->references) === true) {
                $written['*'] = true;
            }
            $this->writes[] = [...($this->writes !== [] ? end($this->writes) : []), ...$written];
        }
        if ($node instanceof Expr\MethodCall && $this->writes !== [] && $this->depth > 0) {
            $node->setAttribute(self::LOOP_WRITES, end($this->writes));
        }

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
            $this->markBodyRoots($node->stmts, $node instanceof Stmt\Foreach_ || ($node instanceof Stmt\For_ && self::countsItems($node)));
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
                $this->markCallback(CallArgs::of($node), $position);
            }
        } elseif ($node instanceof Expr\MethodCall && $node->name instanceof Node\Identifier) {
            $position = self::ITERATING_METHODS[$node->name->toLowerString()] ?? null;
            if ($position !== null) {
                $this->markCallback(CallArgs::of($node), $position);
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
        if (self::isLoop($node) || ($node instanceof Expr\Closure || $node instanceof Expr\ArrowFunction) && $this->closesWrites($node)) {
            array_pop($this->writes);
        }
        if ($node instanceof Node\FunctionLike && !$node instanceof Expr\ArrowFunction) {
            array_pop($this->batches);
            array_pop($this->references);
        }

        return null;
    }

    /**
     * Whether a function makes a reference that a loop could change a variable through: "$a = &$b",
     * "foreach ($list as &$item)", "use (&$x)" or "global".
     */
    private static function takesReferences(Node\FunctionLike $function): bool
    {
        return (new NodeFinder())->findFirst($function->getStmts() ?? [], static fn (Node $n): bool => $n instanceof Expr\AssignRef
            || ($n instanceof Stmt\Foreach_ && $n->byRef)
            || ($n instanceof Expr\ClosureUse && $n->byRef)
            || $n instanceof Stmt\Global_) !== null;
    }

    private static function isLoop(Node $node): bool
    {
        return $node instanceof Stmt\Foreach_ || $node instanceof Stmt\For_ || $node instanceof Stmt\While_ || $node instanceof Stmt\Do_;
    }

    /**
     * Whether a closure pushed a set on entry. It did when a loop encloses it or it is a callback called for each item, which leaving the
     * closure cannot see any more; the set on top then is the closure's own.
     */
    private function closesWrites(Expr\Closure|Expr\ArrowFunction $closure): bool
    {
        return $closure->getAttribute(self::WRITES_PUSHED) === true;
    }

    /**
     * The variable names a loop or closure may change: assigned, incremented, unset, declared static or
     * global, iterated by foreach, caught, a closure's parameters and by-reference uses, and every
     * variable passed to a function, method or constructor other than get(), getById() or load(), which may take
     * it by reference (preg_match(), sort()). "*" when it may change others too: a variable variable, extract(), a call of a closure or
     * other callable held in a variable, include or eval, or a save() or delete(), after which a
     * repository loads again what it had kept.
     *
     * @return array<string, true>
     */
    private static function writtenVariables(Node $node): array
    {
        $names = [];
        $add = static function (?Node $target) use (&$names): void {
            foreach ((new NodeFinder())->findInstanceOf($target !== null ? [$target] : [], Expr\Variable::class) as $variable) {
                $names[is_string($variable->name) ? $variable->name : '*'] = true;
            }
        };
        if ($node instanceof Expr\Closure || $node instanceof Expr\ArrowFunction) {
            $node->setAttribute(self::WRITES_PUSHED, true);
            foreach ($node->params as $param) {
                $add($param->var);
            }
            foreach ($node instanceof Expr\Closure ? $node->uses : [] as $use) {
                if ($use->byRef) {
                    $add($use->var);
                }
            }
        }
        foreach ((new NodeFinder())->find([$node], static fn (Node $n): bool => true) as $n) {
            $targets = match (true) {
                $n instanceof Expr\Assign, $n instanceof Expr\AssignRef, $n instanceof Expr\AssignOp,
                $n instanceof Expr\PreInc, $n instanceof Expr\PostInc, $n instanceof Expr\PreDec, $n instanceof Expr\PostDec => [$n->var],
                $n instanceof Stmt\Foreach_ => [$n->keyVar, $n->valueVar],
                $n instanceof Stmt\Unset_, $n instanceof Stmt\Global_, $n instanceof Stmt\Static_ => $n->vars,
                $n instanceof Stmt\Catch_ => [$n->var],
                // A repository's get() and getById(), and a model's load(), take nothing by reference.
                ($n instanceof Expr\MethodCall || $n instanceof Expr\NullsafeMethodCall) && $n->name instanceof Node\Identifier
                    && in_array($n->name->toLowerString(), ['get', 'getbyid', 'load'], true) => [],
                $n instanceof Expr\FuncCall, $n instanceof Expr\MethodCall, $n instanceof Expr\NullsafeMethodCall,
                $n instanceof Expr\StaticCall, $n instanceof Expr\New_ => array_map(static fn (Node\Arg $arg): Expr => $arg->value, CallArgs::of($n)),
                default => [],
            };
            if (self::changesAnyVariable($n)) {
                $names['*'] = true;
            }
            foreach ($targets as $target) {
                $add($target);
            }
        }

        return $names;
    }

    private static function changesAnyVariable(Node $node): bool
    {
        if ($node instanceof Expr\FuncCall) {
            return !$node->name instanceof Node\Name || in_array($node->name->toLowerString(), ['extract', 'parse_str'], true);
        }
        if ($node instanceof Expr\MethodCall || $node instanceof Expr\NullsafeMethodCall) {
            return !$node->name instanceof Node\Identifier || in_array($node->name->toLowerString(), ['save', 'delete', 'deletebyid'], true);
        }

        return $node instanceof Expr\Include_ || $node instanceof Expr\Eval_;
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
            $this->created[count($this->created) - 1][$node->var->name] = !self::chainHas($node->expr, self::LOADING_METHODS) && !$this->pagesInThisLoop($node->expr);

            return;
        }
        if ($node instanceof Expr\MethodCall && $this->itemDepth === 0 && self::isPaging($node)) {
            $paged = $node->var;
            while ($paged instanceof Expr\MethodCall) {
                $paged = $paged->var;
            }
            if ($paged instanceof Expr\Variable && is_string($paged->name)) {
                foreach (array_keys($this->created) as $i) {
                    unset($this->created[$i][$paged->name]);
                }
            }

            return;
        }
        if (!$node instanceof Expr\MethodCall || !$node->name instanceof Node\Identifier
            || !in_array($node->name->toLowerString(), self::LOADING_METHODS, true)
            || $node->getAttribute(self::COVERED_BY_CHAIN) === true
            // A load the loop is left right after runs once: it must not take the one mark of its collection.
            || $node->getAttribute(LoopExitVisitor::LEAVES_LOOP) === true
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
        if (($createdVariable || $this->chainCallsCreate($node->var)) && !$this->pagesInThisLoop($node->var)) {
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

    /**
     * @param list<string> $methods
     */
    private static function chainHas(Expr $expr, array $methods): bool
    {
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            if ($expr->name instanceof Node\Identifier && in_array($expr->name->toLowerString(), $methods, true)) {
                return true;
            }
        }

        return false;
    }

    private function pagesInThisLoop(Expr $expr): bool
    {
        if ($this->itemDepth > 0) {
            return false;
        }
        for (; $expr instanceof Expr\MethodCall; $expr = $expr->var) {
            if (self::isPaging($expr)) {
                return true;
            }
        }

        return false;
    }

    /** A limit of one row is a lookup, not a page, whatever kind of loop it is in. */
    private static function isPaging(Expr\MethodCall $call): bool
    {
        $position = $call->name instanceof Node\Identifier ? (self::PAGING_METHODS[$call->name->toLowerString()] ?? null) : null;
        if ($position === null) {
            return false;
        }
        $rows = CallArgs::of($call)[$position]->value ?? null;

        return $rows !== null && !($rows instanceof Node\Scalar\LNumber && $rows->value === 1);
    }

    /** for ($i = 0; $i < count($items); $i++) goes over items the way foreach does. */
    private static function countsItems(Stmt\For_ $loop): bool
    {
        return (new NodeFinder())->findFirst(
            $loop->cond,
            static fn (Node $node): bool => $node instanceof Expr\FuncCall && $node->name instanceof Node\Name && in_array($node->name->toLowerString(), ['count', 'sizeof'], true),
        ) !== null;
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
