<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use MagentoPerfRules\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-FPC-05: no-cache or private headers set where the response could otherwise be cached.
 *
 * @implements Rule<MethodCall>
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

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier || !$this->setsNoCache($node)) {
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
                $node->name->toString(),
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
        $header = $call->getArgs()[0]->value ?? null;

        return $name === 'setheader'
            && $header instanceof Node\Scalar\String_
            && in_array(strtolower($header->value), ['cache-control', 'pragma'], true);
    }

    private function servesCacheableRequests(ClassReflection $class): bool
    {
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
