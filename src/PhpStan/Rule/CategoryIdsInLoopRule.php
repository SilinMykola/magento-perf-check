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

    private const PRODUCT = 'Magento\Catalog\Model\Product';
    private const PRODUCT_COLLECTION = 'Magento\Catalog\Model\ResourceModel\Product\Collection';

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
            || !$this->isProduct($node, $scope)
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

    /**
     * Collections have no item type, so the loop variable of "foreach ($productCollection as $product)"
     * is mixed: the iterated collection tells what it is.
     */
    private function isProduct(MethodCall $call, Scope $scope): bool
    {
        if ($this->types->subtypeOf($scope->getType($call->var), self::PRODUCT) !== null) {
            return true;
        }
        $iterated = $call->getAttribute(LoopContextVisitor::ITERATED_EXPRESSION);
        if ($iterated instanceof MethodCall && $iterated->name instanceof Node\Identifier && $iterated->name->toLowerString() === 'getitems') {
            $iterated = $iterated->var;
        }

        return $iterated instanceof Node\Expr
            && $this->types->subtypeOf($scope->getType($iterated), self::PRODUCT_COLLECTION) !== null;
    }
}
