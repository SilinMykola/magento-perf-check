<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ObjectManagerFallbackVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-DI-06: ObjectManager::getInstance() in PHP classes, outside the uses Adobe documents
 * as allowed (constructor fallback, factories, proxies, __wakeup/__sleep).
 *
 * @implements Rule<StaticCall>
 */
final class ObjectManagerUsageRule implements Rule
{
    public const ID = 'P-DI-06';
    public const IDENTIFIER = 'magentoPerf.objectManagerUsage';

    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!ObjectManagerFallbackVisitor::isGetInstance($node)
            || $node->getAttribute(ObjectManagerFallbackVisitor::FALLBACK) === true
            || str_ends_with($scope->getFile(), '.phtml')
        ) {
            return [];
        }
        $class = $scope->getClassReflection()?->getName() ?? '';
        if (preg_match('/(Factory|\\\\Proxy)$/', $class) === 1
            || in_array(strtolower((string) $scope->getFunctionName()), ['__wakeup', '__sleep'], true)
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] ObjectManager::getInstance() is used directly%s. Inject the dependency through the constructor '
                . '(a factory or Proxy if it must be created lazily).',
                self::ID,
                $class !== '' ? ' in ' . $class : '',
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
