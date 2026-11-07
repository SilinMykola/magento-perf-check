<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\CollectionStateVisitor;
use Silin\MagentoPerfCheck\PhpStan\LoopContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-LOOP-05: Product::getCategoryIds() inside a loop runs one query per product, unless
 * addCategoryIds() filled them for the whole collection earlier in the function.
 *
 * @implements Rule<MethodCall>
 */
final class CategoryIdsInLoopRule implements Rule
{
    public const ID = 'P-LOOP-05';
    public const IDENTIFIER = 'magentoPerf.loopCategoryIds';

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
            || !$node->name instanceof Node\Identifier
            || $node->name->toLowerString() !== 'getcategoryids'
            || $node->getAttribute(CollectionStateVisitor::CATEGORY_IDS_ADDED) === true
            // ProductRepository::getList() calls addCategoryIds(), and it returns the interface.
            || !$this->types->isProduct($node, $scope, MagentoTypes::PRODUCT_MODEL)
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] Product::getCategoryIds() is called inside a loop: one query per product. '
                . 'Call addCategoryIds() on the loaded product collection before the loop.',
                self::ID,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
