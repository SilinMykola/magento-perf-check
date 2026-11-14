<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks ObjectManager::getInstance() calls that are the backward-compatible constructor fallback:
 * "$dependency ?: ObjectManager::getInstance()->get(...)", the same with "??" or "??=", or a full
 * ternary such as "$dependency !== null ? $dependency : ...". Adobe documents this pattern as allowed.
 * The same fallback written as "if (!isset($dependency)) { ... }" counts too, and so does
 * "$objectManager = ObjectManager::getInstance()" when only fallbacks use the variable. All of this
 * counts in the constructor only, not in closures declared there.
 *
 * Outside the constructor it marks the lazy getter that fills a missing property once:
 * "if ($this->dependency === null) { $this->dependency = ObjectManager::getInstance()->get(...); }",
 * also through a local variable that the block assigns and then stores in the property, and the same in
 * one expression: "$this->dependency = $this->dependency ?? ...", with "?:", or "$this->dependency ??= ...".
 * It runs once per object, so it costs next to nothing.
 */
final class ObjectManagerFallbackVisitor extends NodeVisitorAbstract
{
    public const FALLBACK = 'magentoPerfObjectManagerFallback';

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Stmt\ClassMethod && $node->name->toLowerString() === '__construct' && $node->stmts !== null) {
            $this->markConstructor($node->params, $node->stmts);

            return null;
        }
        if ($node instanceof Stmt\ClassMethod && $node->stmts !== null) {
            $this->markLazyGetters($node->stmts);
        }

        return null;
    }

    /**
     * @param Node\Param[] $params
     * @param Node\Stmt[] $stmts
     */
    private function markConstructor(array $params, array $stmts): void
    {
        $names = [];
        foreach ($params as $param) {
            if ($param->var instanceof Expr\Variable && is_string($param->var->name)) {
                $names[] = $param->var->name;
            }
        }
        // Variables holding the object manager, which a fallback block may call.
        $objectManagers = [];
        foreach ($this->find($stmts, static fn (Node $node): bool => $node instanceof Expr\Assign) as $assign) {
            /** @var Expr\Assign $assign */
            if ($assign->var instanceof Expr\Variable && is_string($assign->var->name)
                && $this->looksLikeGetInstance($assign->expr)
            ) {
                $objectManagers[] = $assign->var->name;
            }
        }
        // Variables at the root of a fallback, the only uses allowed for a local object manager.
        $inFallback = [];
        foreach ($this->find($stmts, fn (Node $node): bool => $node instanceof Expr && $this->fallbackOf($node, true) !== null) as $node) {
            /** @var Expr $node */
            $expr = $this->fallbackOf($node, true);
            if (!$expr instanceof Expr\MethodCall) {
                // "$a ?: $objectManager" hands the object manager itself on.
                continue;
            }
            while ($expr instanceof Expr\MethodCall) {
                $expr = $expr->var;
            }
            $inFallback[spl_object_id($expr)] = true;
            if ($this->looksLikeGetInstance($expr)) {
                $expr->setAttribute(self::FALLBACK, true);
            }
        }
        foreach ($this->find($stmts, static fn (Node $node): bool => $node instanceof Stmt\If_) as $if) {
            /** @var Stmt\If_ $if */
            $tested = $this->testedAsMissing($if->cond);
            if (!$tested instanceof Expr\Variable || !in_array($tested->name, $names, true)
                || !$this->onlyAssigns($if->stmts, $objectManagers)
            ) {
                continue;
            }
            foreach (self::assignments($if->stmts) as $assign) {
                foreach (self::dependencyRoots($assign->expr) as $root) {
                    $inFallback[spl_object_id($root)] = true;
                    if ($this->looksLikeGetInstance($root)) {
                        $root->setAttribute(self::FALLBACK, true);
                    }
                }
            }
        }
        foreach ($this->find($stmts, static fn (Node $node): bool => $node instanceof Expr\Assign) as $assign) {
            /** @var Expr\Assign $assign */
            if (!$assign->var instanceof Expr\Variable || !is_string($assign->var->name)
                || !$this->looksLikeGetInstance($assign->expr)
            ) {
                continue;
            }
            $name = $assign->var->name;
            // Closures count here: one that captures the object manager takes it out of the fallback.
            $uses = (new NodeFinder())->find($stmts, static fn (Node $node): bool => $node instanceof Expr\Variable
                && $node->name === $name && $node !== $assign->var);
            $onlyFallbacks = $uses !== [];
            foreach ($uses as $use) {
                $onlyFallbacks = $onlyFallbacks && isset($inFallback[spl_object_id($use)]);
            }
            if ($onlyFallbacks) {
                $assign->expr->setAttribute(self::FALLBACK, true);
            }
        }
    }

    /**
     * "if ($this->dependency === null) { $this->dependency = ObjectManager::getInstance()->get(...); }":
     * the block only fills in the property that the condition tests.
     *
     * @param Node\Stmt[] $stmts
     */
    private function markLazyGetters(array $stmts): void
    {
        // The same getter in one expression: "$this->x = $this->x ?? <dependency>", "?:", or "$this->x ??= <dependency>".
        foreach ($this->find($stmts, static fn (Node $node): bool => $node instanceof Expr\Assign
            || $node instanceof Expr\AssignOp\Coalesce) as $assign) {
            /** @var Expr\Assign|Expr\AssignOp\Coalesce $assign */
            $fallback = $assign instanceof Expr\AssignOp\Coalesce ? $assign->expr : $this->fallbackOf($assign->expr, false);
            $tested = match (true) {
                $assign instanceof Expr\AssignOp\Coalesce => $assign->var,
                $assign->expr instanceof Expr\BinaryOp\Coalesce => $assign->expr->left,
                $assign->expr instanceof Expr\Ternary => $assign->expr->cond,
                default => null,
            };
            if ($fallback === null || $tested === null || self::cacheProperty($assign->var) === null
                || !self::sameValue($tested, $assign->var) || !$this->isDependency($fallback, [])
            ) {
                continue;
            }
            foreach (self::dependencyRoots($fallback) as $root) {
                $root->setAttribute(self::FALLBACK, true);
            }
        }
        foreach ($this->find($stmts, static fn (Node $node): bool => $node instanceof Stmt\If_) as $if) {
            /** @var Stmt\If_ $if */
            $tested = $this->testedAsMissing($if->cond);
            $stored = $tested !== null ? $this->storedInData($tested, $if->stmts, $stmts) : null;
            if ($stored !== null) {
                foreach (self::dependencyRoots($stored) as $root) {
                    $root->setAttribute(self::FALLBACK, true);
                }
                continue;
            }
            $property = self::cacheProperty($tested);
            $dependency = $property !== null ? $this->storedInProperty($property, $if->stmts) : null;
            foreach ($dependency !== null ? self::dependencyRoots($dependency) : [] as $root) {
                $root->setAttribute(self::FALLBACK, true);
            }
        }
    }

    /**
     * The dependency of a lazy getter block that only does "$this->property = <dependency>",
     * or "$variable = <dependency>; $this->property = $variable".
     *
     * @param Node\Stmt[] $block
     */
    private function storedInProperty(string $property, array $block): ?Expr
    {
        return $this->storedDependency(
            $block,
            static fn (Expr $expr): ?Expr => $expr instanceof Expr\Assign && self::cacheProperty($expr->var) === $property
                ? $expr->expr : null,
            null,
        );
    }

    /**
     * The dependency that a lazy getter block stores. Comments aside, the block is one statement whose
     * stored value $storedValue picks out, or "$variable = <dependency>;" and then such a statement that
     * stores $variable. With $variable null, any local variable will do.
     *
     * @param Node\Stmt[] $block
     * @param callable(Expr): ?Expr $storedValue the value that an expression statement stores, or null
     */
    private function storedDependency(array $block, callable $storedValue, ?string $variable): ?Expr
    {
        $work = array_values(array_filter($block, static fn (Node\Stmt $stmt): bool => !$stmt instanceof Stmt\Nop));
        $store = $work !== [] ? end($work) : null;
        $value = $store instanceof Stmt\Expression ? $storedValue($store->expr) : null;
        if ($value === null || count($work) > 2) {
            return null;
        }
        if (count($work) === 1) {
            return $this->isDependency($value, []) ? $value : null;
        }
        $first = $work[0];
        if (!$value instanceof Expr\Variable || !is_string($value->name) || ($variable !== null && $value->name !== $variable)
            || !$first instanceof Stmt\Expression || !$first->expr instanceof Expr\Assign
            || !$first->expr->var instanceof Expr\Variable || $first->expr->var->name !== $value->name
            || !$this->isDependency($first->expr->expr, [])
        ) {
            return null;
        }

        return $first->expr->expr;
    }

    /**
     * What a fallback expression falls back to: the right side of "??" or "??=", the else of "?:", or the branch
     * of a full ternary taken when the value it tests is missing, such as "$x !== null ? $x : <fallback>"
     * or "$x === null ? <fallback> : $x", when $fullTernary allows it.
     */
    private function fallbackOf(Expr $node, bool $fullTernary): ?Expr
    {
        if ($node instanceof Expr\BinaryOp\Coalesce) {
            return $node->right;
        }
        if ($node instanceof Expr\AssignOp\Coalesce) {
            return $node->expr;
        }
        if (!$node instanceof Expr\Ternary) {
            return null;
        }
        if ($node->if === null) {
            return $node->else;
        }
        if (!$fullTernary) {
            return null;
        }
        $missing = $this->testedAsMissing($node->cond);
        if ($missing !== null && self::sameValue($missing, $node->else)) {
            return $node->if;
        }
        $present = $this->testedAsPresent($node->cond);
        if ($present !== null && self::sameValue($present, $node->if)) {
            return $node->else;
        }

        return null;
    }

    /**
     * What "$x !== null", "$x != null", "isset($x)", "!empty($x)", "$x", "is_object($x)" or
     * "$x instanceof Type" tests to be there.
     */
    private function testedAsPresent(Expr $cond): ?Expr
    {
        if ($cond instanceof Expr\BinaryOp\NotIdentical || $cond instanceof Expr\BinaryOp\NotEqual) {
            return $this->isNull($cond->right) ? $cond->left : ($this->isNull($cond->left) ? $cond->right : null);
        }
        if ($cond instanceof Expr\BooleanNot && $cond->expr instanceof Expr\Empty_) {
            return $cond->expr->expr;
        }

        return $this->testedAsMissing(new Expr\BooleanNot($cond));
    }

    /**
     * "$name" and "$name", or "$this->name" and "$this->name".
     */
    private static function sameValue(Expr $a, Expr $b): bool
    {
        if ($a instanceof Expr\Variable && $b instanceof Expr\Variable) {
            return is_string($a->name) && $a->name === $b->name;
        }
        $property = self::cacheProperty($a);

        return $property !== null && $property === self::cacheProperty($b);
    }

    /**
     * The same lazy getter kept in the object's data under one key: "if (!$this->getData('key'))",
     * or a variable read once from it, with a block that only does "$this->setData('key', <dependency>)",
     * or "$variable = <dependency>; $this->setData('key', $variable)". Returns that dependency.
     *
     * @param Node\Stmt[] $block
     * @param Node\Stmt[] $method
     */
    private function storedInData(Expr $tested, array $block, array $method): ?Expr
    {
        $key = self::dataKey($tested, 'getdata');
        $variable = $tested instanceof Expr\Variable && is_string($tested->name) ? $tested->name : null;
        if ($key === null && $variable !== null) {
            $keys = [];
            $reads = $this->find($method, static fn (Node $node): bool => $node instanceof Expr\Assign
                && $node->var instanceof Expr\Variable && $node->var->name === $variable);
            foreach ($reads as $assign) {
                /** @var Expr\Assign $assign */
                $read = self::dataKey($assign->expr, 'getdata');
                if ($read !== null) {
                    $keys[$read] = true;
                }
            }
            $key = count($keys) === 1 ? (string) array_key_first($keys) : null;
        }
        if ($key === null) {
            return null;
        }

        // A test of getData() itself leaves no variable to go through: '' matches none.
        return $this->storedDependency(
            $block,
            static fn (Expr $expr): ?Expr => $expr instanceof Expr\MethodCall && self::dataKey($expr, 'setdata') === $key
                ? $expr->getArgs()[1]->value : null,
            $variable ?? '',
        );
    }

    /**
     * The key of "$this->getData('key')" or "$this->setData('key', ...)", by the lower-case method name.
     */
    private static function dataKey(Expr $expr, string $method): ?string
    {
        if (!$expr instanceof Expr\MethodCall || !$expr->var instanceof Expr\Variable || $expr->var->name !== 'this'
            || !$expr->name instanceof Node\Identifier || $expr->name->toLowerString() !== $method
        ) {
            return null;
        }
        $args = $expr->getArgs();
        if (count($args) !== ($method === 'setdata' ? 2 : 1) || !$args[0]->value instanceof Node\Scalar\String_) {
            return null;
        }

        return $args[0]->value->value;
    }

    /**
     * The assignments of a block that onlyAssigns() accepted.
     *
     * @param Node\Stmt[] $stmts
     * @return list<Expr\Assign>
     */
    private static function assignments(array $stmts): array
    {
        $assignments = [];
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Stmt\Expression && $stmt->expr instanceof Expr\Assign) {
                $assignments[] = $stmt->expr;
            }
        }

        return $assignments;
    }

    /**
     * What the calls of a dependency that isDependency() accepted are made on: the object manager,
     * or a variable holding it. Calls in their arguments are not part of the fallback.
     *
     * @return list<Expr>
     */
    private static function dependencyRoots(Expr $expr): array
    {
        if (!$expr instanceof Expr\Array_) {
            return $expr instanceof Expr\MethodCall ? [$expr->var] : [];
        }
        $roots = [];
        foreach ($expr->items as $item) {
            $roots = [...$roots, ...self::dependencyRoots($item->value)];
        }

        return $roots;
    }

    /**
     * A property a lazy getter can keep the object in: "name" for "$this->name", "::name" for "self::$name"
     * or "static::$name", which keep it once per process; null for anything else.
     */
    private static function cacheProperty(?Expr $expr): ?string
    {
        if ($expr instanceof Expr\StaticPropertyFetch && $expr->class instanceof Node\Name
            && in_array($expr->class->toLowerString(), ['self', 'static'], true) && $expr->name instanceof Node\VarLikeIdentifier
        ) {
            return '::' . $expr->name->toString();
        }

        return $expr instanceof Expr\PropertyFetch && $expr->var instanceof Expr\Variable && $expr->var->name === 'this'
            && $expr->name instanceof Node\Identifier ? $expr->name->toString() : null;
    }

    /**
     * A fallback block only fills in dependencies; anything else in it is real work.
     *
     * @param Node\Stmt[] $stmts
     * @param string[] $objectManagers
     */
    private function onlyAssigns(array $stmts, array $objectManagers): bool
    {
        foreach ($stmts as $stmt) {
            // A comment left alone at the end of a block, such as "// phpcs:enable", is a Nop.
            if ($stmt instanceof Stmt\Nop) {
                continue;
            }
            if (!$stmt instanceof Stmt\Expression || !$stmt->expr instanceof Expr\Assign
                || !$this->isPlainTarget($stmt->expr->var)
                || !$this->isDependency($stmt->expr->expr, $objectManagers)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * "$variable" or "$this->property".
     */
    private function isPlainTarget(Expr $expr): bool
    {
        return ($expr instanceof Expr\Variable && is_string($expr->name))
            || ($expr instanceof Expr\PropertyFetch && $expr->var instanceof Expr\Variable
                && $expr->var->name === 'this');
    }

    /**
     * One call on the object manager, such as "->get(...)", or an array of these.
     *
     * @param string[] $objectManagers
     */
    private function isDependency(Expr $expr, array $objectManagers): bool
    {
        if ($expr instanceof Expr\Array_) {
            foreach ($expr->items as $item) {
                // @phpstan-ignore identical.alwaysFalse (php-parser 4, bundled with PHPStan 1.12, allows null items)
                if ($item === null || !$this->isDependency($item->value, $objectManagers)) {
                    return false;
                }
            }

            return true;
        }
        if (!$expr instanceof Expr\MethodCall) {
            return false;
        }

        return $this->looksLikeGetInstance($expr->var)
            || ($expr->var instanceof Expr\Variable && in_array($expr->var->name, $objectManagers, true));
    }

    /**
     * Like NodeFinder::find(), but closures, functions and classes declared in the
     * constructor are left out: their code does not run as the constructor's.
     *
     * @param mixed[] $nodes
     * @param callable(Node): bool $filter
     * @return Node[]
     */
    private function find(array $nodes, callable $filter): array
    {
        $found = [];
        foreach ($nodes as $node) {
            if (is_array($node)) {
                $found = [...$found, ...$this->find($node, $filter)];
                continue;
            }
            if (!$node instanceof Node) {
                continue;
            }
            if ($filter($node)) {
                $found[] = $node;
            }
            if ($node instanceof Expr\Closure || $node instanceof Expr\ArrowFunction
                || $node instanceof Stmt\Function_ || $node instanceof Stmt\ClassLike
            ) {
                continue;
            }
            foreach ($node->getSubNodeNames() as $name) {
                $found = [...$found, ...$this->find([$node->$name], $filter)];
            }
        }

        return $found;
    }

    /**
     * What "!isset($x)", "empty($x)", "$x === null", "$x == null", "!$x", "is_null($x)",
     * "!is_object($x)" or "!$x instanceof Type" tests to be missing.
     */
    private function testedAsMissing(Expr $cond): ?Expr
    {
        return match (true) {
            $cond instanceof Expr\BooleanNot && $cond->expr instanceof Expr\Isset_ && count($cond->expr->vars) === 1
                => $cond->expr->vars[0],
            $cond instanceof Expr\BooleanNot && $cond->expr instanceof Expr\Instanceof_ => $cond->expr->expr,
            $cond instanceof Expr\BooleanNot && $cond->expr instanceof Expr\FuncCall && $cond->expr->name instanceof Node\Name
                && $cond->expr->name->toLowerString() === 'is_object' && count($cond->expr->getArgs()) === 1
                => $cond->expr->getArgs()[0]->value,
            $cond instanceof Expr\BooleanNot => $cond->expr,
            $cond instanceof Expr\Empty_ => $cond->expr,
            $cond instanceof Expr\BinaryOp\Identical, $cond instanceof Expr\BinaryOp\Equal
                => $this->isNull($cond->right) ? $cond->left : ($this->isNull($cond->left) ? $cond->right : null),
            $cond instanceof Expr\FuncCall && $cond->name instanceof Node\Name
                && $cond->name->toLowerString() === 'is_null' && count($cond->getArgs()) === 1 => $cond->getArgs()[0]->value,
            default => null,
        };
    }

    private function isNull(Expr $expr): bool
    {
        return $expr instanceof Expr\ConstFetch && $expr->name->toLowerString() === 'null';
    }

    /**
     * Names are not resolved yet when this visitor runs, so match "...ObjectManager::getInstance()"
     * by its last segment; the rule checks the fully qualified name.
     */
    private function looksLikeGetInstance(Expr $expr): bool
    {
        return $expr instanceof Expr\StaticCall
            && $expr->class instanceof Node\Name
            && $expr->name instanceof Node\Identifier
            && $expr->name->toLowerString() === 'getinstance'
            && strtolower($expr->class->getLast()) === 'objectmanager';
    }

    public static function isGetInstance(Expr\StaticCall $call): bool
    {
        return $call->class instanceof Node\Name
            && $call->name instanceof Node\Identifier
            && $call->name->toLowerString() === 'getinstance'
            && strtolower(ltrim($call->class->toString(), '\\')) === 'magento\framework\app\objectmanager';
    }
}
