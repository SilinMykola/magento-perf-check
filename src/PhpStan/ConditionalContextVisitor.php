<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks every node inside a branch of an if statement with IN_CONDITIONAL. The condition of
 * the if is not marked, because it always runs; the condition of an elseif may not run, so it is.
 *
 * Marks every node in the fallback of `$a ?: $b` or `$a ?? $b`, the `$b` that runs only when `$a`
 * is empty, with IN_FALLBACK. Not when `$a` reads a property (or an element of one) that the class
 * declares private and never fills: no default other than `[]` or `null`, no assignment of another
 * value to the property (nor `++` or `--`) and none to an element of it, and no trait used by the class.
 * Such a property stays empty, so its fallback runs on every call. Any other property may be filled where
 * the rule does not look, by a parent or child class, by the class that uses a trait or by a method of a
 * trait the class uses, so its fallback is marked.
 */
final class ConditionalContextVisitor extends NodeVisitorAbstract
{
    public const IN_CONDITIONAL = 'magentoPerfInConditional';

    public const IN_FALLBACK = 'magentoPerfInFallback';

    private const BRANCH = 'magentoPerfConditionalBranch';

    private const FALLBACK = 'magentoPerfFallback';

    private int $depth = 0;

    private int $fallbackDepth = 0;

    /** @var list<array{string, array<string, true>}> the name and the unfilled properties of each enclosing class, innermost last */
    private array $unfilled = [];

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->depth = 0;
        $this->fallbackDepth = 0;
        $this->unfilled = [];

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node->getAttribute(self::BRANCH) === true) {
            ++$this->depth;
        }
        if ($this->depth > 0) {
            $node->setAttribute(self::IN_CONDITIONAL, true);
        }
        if ($node instanceof Stmt\If_) {
            foreach ([...$node->stmts, ...$node->elseifs, ...($node->else === null ? [] : [$node->else])] as $branch) {
                $branch->setAttribute(self::BRANCH, true);
            }
        }
        if ($node->getAttribute(self::FALLBACK) === true) {
            ++$this->fallbackDepth;
        }
        if ($this->fallbackDepth > 0) {
            $node->setAttribute(self::IN_FALLBACK, true);
        }
        if ($node instanceof Stmt\ClassLike) {
            // Unset on an anonymous class, whose properties are read through self:: or static:: only.
            $name = isset($node->namespacedName) ? $node->namespacedName->toString() : '';
            $this->unfilled[] = [$name, self::unfilledProperties($node, $name)];
        }
        if ($node instanceof Expr\BinaryOp\Coalesce && !$this->readsUnfilled($node->left)) {
            $node->right->setAttribute(self::FALLBACK, true);
        } elseif ($node instanceof Expr\Ternary && $node->if === null && !$this->readsUnfilled($node->cond)) {
            $node->else->setAttribute(self::FALLBACK, true);
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node->getAttribute(self::BRANCH) === true) {
            --$this->depth;
        }
        if ($node->getAttribute(self::FALLBACK) === true) {
            --$this->fallbackDepth;
        }
        if ($node instanceof Stmt\ClassLike) {
            array_pop($this->unfilled);
        }

        return null;
    }

    private function readsUnfilled(Expr $expr): bool
    {
        $class = end($this->unfilled);
        if ($class === false) {
            return false;
        }
        [$name, $unfilled] = $class;
        $property = MemoizingMethodCollector::property($expr, $name);

        return $property !== null && isset($unfilled[$property]);
    }

    /**
     * @return array<string, true> the private properties a class declares and never fills, keyed as
     *                             MemoizingMethodCollector::property() names them
     */
    private static function unfilledProperties(Stmt\ClassLike $class, string $name): array
    {
        $unfilled = [];
        if ($class instanceof Stmt\Trait_) {
            return $unfilled;
        }
        foreach ($class->stmts as $statement) {
            // A method of a trait the class uses may fill any of its properties.
            if ($statement instanceof Stmt\TraitUse) {
                return $unfilled;
            }
        }
        foreach ($class->getProperties() as $declaration) {
            if (!$declaration->isPrivate()) {
                continue;
            }
            foreach ($declaration->props as $property) {
                if ($property->default === null || self::isEmpty($property->default)) {
                    $unfilled[($declaration->isStatic() ? '::' : '') . $property->name->toString()] = true;
                }
            }
        }
        $assigns = (new NodeFinder())->find($class->stmts, static fn (Node $node): bool => $node instanceof Expr\Assign
            || $node instanceof Expr\AssignRef || $node instanceof Expr\AssignOp
            || $node instanceof Expr\PreInc || $node instanceof Expr\PostInc || $node instanceof Expr\PreDec || $node instanceof Expr\PostDec);
        foreach ($assigns as $assign) {
            /** @var Expr\Assign|Expr\AssignRef|Expr\AssignOp|Expr\PreInc|Expr\PostInc|Expr\PreDec|Expr\PostDec $assign */
            $property = MemoizingMethodCollector::property($assign->var, $name);
            $empties = $assign instanceof Expr\Assign && !$assign->var instanceof Expr\ArrayDimFetch && self::isEmpty($assign->expr);
            if ($property !== null && !$empties) {
                unset($unfilled[$property]);
            }
        }

        return $unfilled;
    }

    private static function isEmpty(Expr $expr): bool
    {
        return ($expr instanceof Expr\Array_ && $expr->items === [])
            || ($expr instanceof Expr\ConstFetch && $expr->name->toLowerString() === 'null');
    }
}
