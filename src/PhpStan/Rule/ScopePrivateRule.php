<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-FPC-01: $_isScopePrivate is obsolete and does not work properly.
 *
 * @implements Rule<Node>
 */
final class ScopePrivateRule implements Rule
{
    public const ID = 'P-FPC-01';
    public const IDENTIFIER = 'magentoPerf.isScopePrivate';

    private const ABSTRACT_BLOCK = 'Magento\Framework\View\Element\AbstractBlock';
    private const PROPERTY = '_isScopePrivate';

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $declares = false;
        if ($node instanceof Node\Stmt\Property) {
            foreach ($node->props as $property) {
                $declares = $declares || ($property->name->toString() === self::PROPERTY && self::isTrue($property->default));
            }
        }
        $assigns = $node instanceof Node\Expr\Assign
            && $node->var instanceof Node\Expr\PropertyFetch
            && $node->var->name instanceof Node\Identifier
            && $node->var->name->toString() === self::PROPERTY
            && self::isTrue($node->expr);
        // AbstractBlock itself declares the (deprecated) property; only subclasses use it.
        $class = $scope->getClassReflection();
        if ((!$declares && !$assigns) || $class === null || !$class->isSubclassOf(self::ABSTRACT_BLOCK)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.',
                self::ID,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    /** False is the default value and marks nothing private. */
    private static function isTrue(?Node\Expr $value): bool
    {
        return $value instanceof Node\Expr\ConstFetch && $value->name->toLowerString() === 'true';
    }
}
