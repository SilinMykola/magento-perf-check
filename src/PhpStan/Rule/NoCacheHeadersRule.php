<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-FPC-05: no-cache or private headers set where the response could otherwise be cached.
 * One finding per statement, however many headers the statement sets.
 *
 * @implements Rule<Stmt>
 */
final class NoCacheHeadersRule implements Rule
{
    public const ID = 'P-FPC-05';
    public const IDENTIFIER = 'magentoPerf.noCacheHeaders';

    private const ACTION = 'Magento\Framework\App\ActionInterface';
    private const GET_ACTION = 'Magento\Framework\App\Action\HttpGetActionInterface';
    private const RESOLVERS = [
        'Magento\Framework\GraphQl\Query\ResolverInterface',
        'Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface',
    ];

    /** Core endpoints that are private by design (class name prefixes). */
    private const PRIVATE_ENDPOINTS = [
        'Magento\Customer\Controller\Section\Load',
        'Magento\PageCache\Controller\Block\Render',
        'Magento\Downloadable\Controller\Download\\',
    ];

    public function getNodeType(): string
    {
        return Stmt::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Stmt\Expression && !$node instanceof Stmt\Return_) {
            return [];
        }
        $call = (new NodeFinder())->findFirst(
            [$node],
            fn (Node $n): bool => $n instanceof MethodCall && $this->setsNoCache($n),
        );
        if (!$call instanceof MethodCall || !$call->name instanceof Node\Identifier) {
            return [];
        }
        $class = $scope->getClassReflection();
        if ($class === null || !$this->servesCacheableRequests($class)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s() in %s makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.',
                self::ID,
                $call->name->toString(),
                $class->getName(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    private function setsNoCache(MethodCall $call): bool
    {
        $name = $call->name instanceof Node\Identifier ? $call->name->toLowerString() : '';
        if ($name === 'setnocacheheaders' || $name === 'setprivateheaders') {
            return true;
        }
        $args = $call->getArgs();
        $header = $args[0]->value ?? null;
        if ($name !== 'setheader'
            || !$header instanceof Node\Scalar\String_
            || !in_array(strtolower($header->value), ['cache-control', 'pragma'], true)
        ) {
            return false;
        }
        $value = $args[1]->value ?? null;

        // A computed value may disable caching; a literal one only when it says so.
        return !$value instanceof Node\Scalar\String_
            || preg_match('/no-cache|no-store|private/i', $value->value) === 1;
    }

    private function servesCacheableRequests(ClassReflection $class): bool
    {
        foreach (self::PRIVATE_ENDPOINTS as $prefix) {
            if (str_starts_with($class->getName(), $prefix)) {
                return false;
            }
        }
        foreach (self::RESOLVERS as $resolver) {
            if ($class->implementsInterface($resolver)) {
                return true;
            }
        }
        if (!$class->implementsInterface(self::ACTION) || str_contains($class->getName(), '\\Controller\\Adminhtml\\')) {
            return false;
        }
        if ($class->implementsInterface(self::GET_ACTION)) {
            return true;
        }
        // A controller that declares no HTTP method interface answers every method, GET included.
        foreach (array_keys($class->getInterfaces()) as $interface) {
            if (preg_match('/^Magento\\\\Framework\\\\App\\\\Action\\\\Http\\w+ActionInterface$/', $interface) === 1) {
                return false;
            }
        }

        return true;
    }
}
