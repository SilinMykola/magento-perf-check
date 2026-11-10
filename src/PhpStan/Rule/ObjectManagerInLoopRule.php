<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\LoopContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\LoopExitVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * P-LOOP-11: the object manager asked for an object inside a loop, through
 * ObjectManager::getInstance() or an injected ObjectManagerInterface. Factories and
 * proxies, which Adobe allows to use it, are left out, as in P-DI-06.
 *
 * @implements Rule<MethodCall>
 */
final class ObjectManagerInLoopRule implements Rule
{
    public const ID = 'P-LOOP-11';
    public const IDENTIFIER = 'magentoPerf.loopObjectManager';

    private const INTERFACE = 'Magento\Framework\ObjectManagerInterface';

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->getAttribute(LoopContextVisitor::IN_LOOP) !== true
            || $node->getAttribute(LoopExitVisitor::LEAVES_LOOP) === true
            || !$node->name instanceof Node\Identifier
            || !in_array($node->name->toLowerString(), ['get', 'create'], true)
            || !(new ObjectType(self::INTERFACE))->isSuperTypeOf($scope->getType($node->var))->yes()
            || preg_match('/(Factory|\\\\Proxy)$/', $scope->getClassReflection()?->getName() ?? '') === 1
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] ObjectManager::%s() is called inside a loop. Inject the dependency through the constructor, '
                . 'or a factory when each iteration needs a new object.',
                self::ID,
                $node->name->toString(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
