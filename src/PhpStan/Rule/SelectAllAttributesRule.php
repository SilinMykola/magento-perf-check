<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-COL-03: addAttributeToSelect('*') reads the values of every attribute of the entity.
 *
 * @implements Rule<MethodCall>
 */
final class SelectAllAttributesRule implements Rule
{
    public const ID = 'P-COL-03';
    public const IDENTIFIER = 'magentoPerf.selectAllAttributes';

    private const EAV_COLLECTION = 'Magento\Eav\Model\Entity\Collection\AbstractCollection';

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $attribute = $node->getArgs()[0]->value ?? null;
        if (!$node->name instanceof Node\Identifier
            || $node->name->toLowerString() !== 'addattributetoselect'
            || !$attribute instanceof Node\Scalar\String_
            || $attribute->value !== '*'
            || !MagentoTypes::isObjectOf($scope->getType($node->var), self::EAV_COLLECTION)
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                "[%s] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.",
                self::ID,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
