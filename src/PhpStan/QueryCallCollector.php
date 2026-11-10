<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;

/**
 * For P-LOOP-12: a call in a class method that runs one query (QueryCall), with the method it is in.
 * `list` marks a getList(), which P-LOOP-09 reports only once per item, not in a paging loop.
 * A query in a closure counts only when the closure runs in the method's own loop: a closure that
 * the method returns or stores runs no query when the method is called.
 *
 * @implements Collector<MethodCall, array{class: string, method: string, query: string, line: int, file: string, list: bool}>
 */
final class QueryCallCollector implements Collector
{
    private readonly QueryCall $queries;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->queries = new QueryCall($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): ?array
    {
        $method = $scope->getFunction();
        $class = $scope->getClassReflection();
        if (!$method instanceof MethodReflection || $class === null
            || ($scope->isInAnonymousFunction() && $node->getAttribute(LoopContextVisitor::IN_LOOP) !== true)
        ) {
            return null;
        }
        $query = $this->queries->describe($node, $scope);

        return $query === null ? null : [
            'class' => $class->getName(),
            'method' => $method->getName(),
            'query' => $query,
            'line' => $node->getStartLine(),
            // In a trait, getFile() is the file of the class that uses it.
            'file' => basename($scope->getTraitReflection()?->getFileName() ?? $scope->getFile()),
            'list' => $this->queries->repositoryList($node, $scope) !== null,
        ];
    }
}
