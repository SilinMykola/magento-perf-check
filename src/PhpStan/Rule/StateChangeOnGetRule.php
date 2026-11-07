<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ControllerResultVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-FPC-06: a GET-only controller that changes state. GET responses may be cached and
 * replayed, so the change happens once, or for the wrong visitor.
 *
 * @implements Rule<MethodCall>
 */
final class StateChangeOnGetRule implements Rule
{
    public const ID = 'P-FPC-06';
    public const IDENTIFIER = 'magentoPerf.stateChangeOnGet';

    private const GET_ACTION = 'Magento\Framework\App\Action\HttpGetActionInterface';
    private const POST_ACTION = 'Magento\Framework\App\Action\HttpPostActionInterface';
    private const CART_CLASSES = ['Magento\Checkout\Model\Cart', 'Magento\Quote\Model\Quote'];

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
        $class = $scope->getClassReflection();
        if (!$node->name instanceof Node\Identifier
            || strtolower((string) $scope->getFunctionName()) !== 'execute'
            || !MagentoTypes::implements($class, self::GET_ACTION)
            || MagentoTypes::implements($class, self::POST_ACTION)
            || str_contains((string) $class?->getName(), '\\Controller\\Adminhtml\\')
            || $node->getAttribute(ControllerResultVisitor::IN_UNCACHEABLE_EXECUTE) === true
        ) {
            return [];
        }
        $method = $node->name->toLowerString();
        $type = $scope->getType($node->var);
        $target = match ($method) {
            'save', 'delete' => $this->types->subtypeOf($type, MagentoTypes::ABSTRACT_MODEL)
                ?? $this->types->resourceModel($type)
                ?? $this->types->repository($type),
            'deletebyid' => $this->types->repository($type),
            'addproduct' => $this->types->subtypeOf($type, self::CART_CLASSES[0]) ?? $this->types->subtypeOf($type, self::CART_CLASSES[1]),
            default => null,
        };
        if ($target === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s handles GET only but calls %s::%s(). Change state only on POST or PUT: '
                . 'GET responses can be cached and served without running this code.',
                self::ID,
                $class?->getName() ?? 'This controller',
                $target,
                $node->name->toString(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
