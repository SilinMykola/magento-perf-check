<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-DI-03: dispatching an event from a constructor runs every observer of that event
 * whenever the object is created.
 *
 * @implements Rule<MethodCall>
 */
final class DispatchInConstructorRule implements Rule
{
    public const ID = 'P-DI-03';
    public const IDENTIFIER = 'magentoPerf.dispatchInConstructor';

    private const EVENT_MANAGER = 'Magento\Framework\Event\ManagerInterface';

    private readonly MagentoTypes $types;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->types = new MagentoTypes($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier
            || $node->name->toLowerString() !== 'dispatch'
            || strtolower((string) $scope->getFunctionName()) !== '__construct'
            || $this->types->subtypeOf($scope->getType($node->var), self::EVENT_MANAGER) === null
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] An event is dispatched in the constructor of %s: its observers run every time the object is created. '
                . 'Dispatch it from the method where the event happens.',
                self::ID,
                $scope->getClassReflection()?->getName() ?? 'this class',
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
