<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassMethodNode;

/**
 * For P-LOOP-12: the lines of a method whose result the method keeps for later calls, so a query
 * there runs once, not on every call:
 * - the value stored in an element of a property, `$this->cache[$id] = ...` or `self::$cache[$id] ??= ...`;
 * - the value of `$this->product ??= ...`;
 * - the body of an `if` that reads a property and assigns it (a lazy getter);
 * - the value assigned to a property after an `if` on that property that returns early.
 * An append, `$this->log[] = ...`, keeps nothing for a later call.
 *
 * @implements Collector<InClassMethodNode, array{class: string, method: string, kept: list<array{int, int}>}>
 */
final class MemoizingMethodCollector implements Collector
{
    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): ?array
    {
        $class = $scope->getClassReflection();
        $method = $node->getOriginalNode();
        if ($class === null) {
            return null;
        }
        $finder = new NodeFinder();
        $stmts = $method->stmts ?? [];

        $kept = [];
        $returnsEarly = [];
        foreach ($finder->findInstanceOf($stmts, Stmt\If_::class) as $if) {
            $read = self::readProperties($if->cond);
            if ($read === [] || $if->stmts === []) {
                continue;
            }
            $assigns = $finder->findFirst($if->stmts, static fn (Node $node): bool => $node instanceof Expr\Assign
                && isset($read[(string) self::property($node->var)])) !== null;
            if ($assigns) {
                $kept[] = [$if->stmts[0]->getStartLine(), $if->stmts[count($if->stmts) - 1]->getEndLine()];
            } elseif ($finder->findFirstInstanceOf($if->stmts, Stmt\Return_::class) !== null) {
                $returnsEarly += $read;
            }
        }
        foreach ($finder->find($stmts, static fn (Node $node): bool => $node instanceof Expr\Assign || $node instanceof Expr\AssignOp\Coalesce) as $assign) {
            /** @var Expr\Assign|Expr\AssignOp\Coalesce $assign */
            $target = $assign->var;
            $property = self::property($target);
            if ($property === null) {
                continue;
            }
            $keeps = $target instanceof Expr\ArrayDimFetch
                ? $target->dim !== null
                : $assign instanceof Expr\AssignOp\Coalesce || isset($returnsEarly[$property]);
            if ($keeps) {
                $kept[] = [$assign->expr->getStartLine(), $assign->expr->getEndLine()];
            }
        }

        return $kept === [] ? null : ['class' => $class->getName(), 'method' => $method->name->toString(), 'kept' => $kept];
    }

    /**
     * @return array<string, true> the properties an expression reads
     */
    private static function readProperties(Expr $expr): array
    {
        $read = [];
        foreach ((new NodeFinder())->find($expr, static fn (Node $node): bool => self::property($node) !== null) as $fetch) {
            $read[(string) self::property($fetch)] = true;
        }

        return $read;
    }

    /** "$name" for `$this->name` and "::name" for `self::$name` or `static::$name`, also under array offsets. */
    private static function property(Node $node): ?string
    {
        while ($node instanceof Expr\ArrayDimFetch) {
            $node = $node->var;
        }
        if ($node instanceof Expr\PropertyFetch && $node->var instanceof Expr\Variable && $node->var->name === 'this'
            && $node->name instanceof Node\Identifier
        ) {
            return $node->name->toString();
        }
        if ($node instanceof Expr\StaticPropertyFetch && $node->name instanceof Node\VarLikeIdentifier) {
            return '::' . $node->name->toString();
        }

        return null;
    }
}
