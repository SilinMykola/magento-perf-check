<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * T-01: ->getLayout()->createBlock() in a template.
 *
 * @implements Rule<MethodCall>
 */
final class CreateBlockInTemplateRule implements Rule
{
    public const ID = 'T-01';
    public const IDENTIFIER = 'magentoPerf.createBlockInTemplate';

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!str_ends_with($scope->getFile(), '.phtml')
            || !$node->name instanceof Node\Identifier || $node->name->toLowerString() !== 'createblock'
            || !$node->var instanceof MethodCall
            || !$node->var->name instanceof Node\Identifier || $node->var->name->toLowerString() !== 'getlayout'
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] The template creates a block with getLayout()->createBlock(). The Technical guidelines forbid this; '
                . 'declare the block in layout XML and render it with getChildHtml(), or use a view model.',
                self::ID,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
