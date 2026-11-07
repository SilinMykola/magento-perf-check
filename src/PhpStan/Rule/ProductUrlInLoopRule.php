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
 * P-LOOP-04: without a request path on the product, building its URL looks the rewrite up
 * with one query, unless addUrlRewrite() filled the paths for the collection earlier in the
 * function.
 *
 * @implements Rule<MethodCall>
 */
final class ProductUrlInLoopRule implements Rule
{
    public const ID = 'P-LOOP-04';
    public const IDENTIFIER = 'magentoPerf.loopProductUrl';

    private const PRODUCT_URL = 'Magento\Catalog\Model\Product\Url';

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
            || $node->getAttribute(CollectionStateVisitor::URL_REWRITE_ADDED) === true
            || !$this->buildsProductUrl($node, $node->name->toLowerString(), $scope)
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s() builds a product URL inside a loop: one URL rewrite query per product without a request path. '
                . 'Call addUrlRewrite() on the product collection.',
                self::ID,
                $node->name->toString(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    private function buildsProductUrl(MethodCall $call, string $method, Scope $scope): bool
    {
        if ($this->types->subtypeOf($scope->getType($call->var), self::PRODUCT_URL) !== null) {
            return in_array($method, ['getproducturl', 'geturl', 'geturlinstore'], true);
        }

        return in_array($method, ['getproducturl', 'geturlinstore'], true) && $this->types->isProduct($call, $scope);
    }
}
