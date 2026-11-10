<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;

/**
 * For P-LOOP-12: a method call in loop context (`->` or `::`), with the class that declares the
 * called method, whether the loop runs once per item, and whether the receiver may be a new object in
 * each iteration: the foreach value, something reached from it, `new` or a `create()` result. PHPStan
 * also hands a `?->` call to collectors
 * as a `->` call, so it is not collected twice. A call that is itself a query is left out: P-LOOP-01,
 * P-LOOP-09 or P-LOOP-10 report it.
 *
 * @implements Collector<Expr\CallLike, array{class: string, method: string, line: int, item: bool, perItem: bool}>
 */
final class LoopCallCollector implements Collector
{
    private readonly QueryCall $queries;

    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
        $this->queries = new QueryCall($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return Expr\CallLike::class;
    }

    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!($node instanceof Expr\MethodCall || $node instanceof Expr\StaticCall)
            || $node->getAttribute(LoopContextVisitor::IN_LOOP) !== true
            || $node->getAttribute(LoopExitVisitor::LEAVES_LOOP) === true
            || !$node->name instanceof Node\Identifier
            || ($node instanceof Expr\MethodCall && $this->queries->describe($node, $scope) !== null)
        ) {
            return null;
        }
        $class = $this->receiver($node, $scope);
        $name = $node->name->toString();
        if ($class === null || !$class->hasMethod($name)) {
            return null;
        }
        $method = $class->getMethod($name, $scope);

        return [
            'class' => $method->getDeclaringClass()->getName(),
            'method' => $method->getName(),
            'line' => $node->getStartLine(),
            'item' => $node->getAttribute(LoopContextVisitor::IN_ITEM_LOOP) === true,
            'perItem' => $node instanceof Expr\MethodCall && self::perItem($node),
        ];
    }

    /** True when the receiver may be another object in each iteration. */
    private static function perItem(Expr\MethodCall $call): bool
    {
        if ($call->getAttribute(LoopContextVisitor::ITERATED_EXPRESSION) !== null) {
            return true;
        }
        $expr = $call->var;
        while (true) {
            if ($expr instanceof Expr\MethodCall || $expr instanceof Expr\NullsafeMethodCall) {
                if ($expr->getAttribute(LoopContextVisitor::ITERATED_EXPRESSION) !== null
                    || ($expr->name instanceof Node\Identifier && $expr->name->toLowerString() === 'create')
                ) {
                    return true;
                }
                $expr = $expr->var;
            } elseif ($expr instanceof Expr\PropertyFetch || $expr instanceof Expr\NullsafePropertyFetch || $expr instanceof Expr\ArrayDimFetch) {
                $expr = $expr->var;
            } else {
                return $expr instanceof Expr\New_;
            }
        }
    }

    private function receiver(Expr\MethodCall|Expr\StaticCall $call, Scope $scope): ?ClassReflection
    {
        if ($call instanceof Expr\StaticCall && $call->class instanceof Node\Name) {
            // Also resolves self, static and parent.
            $classNames = [$scope->resolveName($call->class)];
        } else {
            $classNames = $scope->getType($call instanceof Expr\StaticCall ? $call->class : $call->var)->getObjectClassNames();
        }
        if (count($classNames) !== 1 || !$this->reflectionProvider->hasClass($classNames[0])) {
            return null;
        }

        return $this->reflectionProvider->getClass($classNames[0]);
    }
}
