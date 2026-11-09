<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\LoopContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\LoopExitVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-LOOP-06: the table source model loads an option collection on every getOptionText(), and
 * getAttributeText() goes through it for attributes with that source, the default for a select.
 *
 * @implements Rule<MethodCall>
 */
final class AttributeTextInLoopRule implements Rule
{
    public const ID = 'P-LOOP-06';
    public const IDENTIFIER = 'magentoPerf.loopAttributeText';

    private const TABLE_SOURCE = 'Magento\Eav\Model\Entity\Attribute\Source\Table';

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
        if ($node->getAttribute(LoopContextVisitor::IN_LOOP) !== true
            || $node->getAttribute(LoopExitVisitor::LEAVES_LOOP) === true
            || !$node->name instanceof Node\Identifier
        ) {
            return [];
        }
        $reported = match ($node->name->toLowerString()) {
            'getattributetext' => $this->types->isProduct($node, $scope),
            'getoptiontext' => MagentoTypes::isObjectOf($scope->getType($node->var), self::TABLE_SOURCE),
            default => false,
        };
        if (!$reported) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s() is called inside a loop: with the table source model, the default for a select attribute, one query per call. '
                . 'Load the options once with getAllOptions() and look labels up in that array.',
                self::ID,
                $node->name->toString(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
