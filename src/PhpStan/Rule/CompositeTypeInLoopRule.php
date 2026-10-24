<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\LoopContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-LOOP-07: composite product type methods (and getIdBySku) inside a loop; each call
 * queries child or parent products for one product.
 *
 * @implements Rule<MethodCall>
 */
final class CompositeTypeInLoopRule implements Rule
{
    public const ID = 'P-LOOP-07';
    public const IDENTIFIER = 'magentoPerf.loopCompositeType';

    private const TYPE_METHODS = [
        'getusedproducts',
        'getconfigurableattributes',
        'getchildrenids',
        'getparentidsbychild',
        'getusedproductids',
        'getassociatedproducts',
    ];

    private const TYPE_CLASSES = [
        'Magento\Catalog\Model\Product\Type\AbstractType',
        'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable',
    ];

    private const SKU_CLASSES = [
        'Magento\Catalog\Model\Product',
        'Magento\Catalog\Model\ResourceModel\Product',
    ];

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
        if ($node->getAttribute(LoopContextVisitor::IN_LOOP) !== true || !$node->name instanceof Node\Identifier) {
            return [];
        }
        $method = $node->name->toLowerString();
        $candidates = match (true) {
            in_array($method, self::TYPE_METHODS, true) => self::TYPE_CLASSES,
            $method === 'getidbysku' => self::SKU_CLASSES,
            default => [],
        };
        $type = $scope->getType($node->var);
        foreach ($candidates as $candidate) {
            $class = $this->types->subtypeOf($type, $candidate);
            if ($class !== null) {
                return [
                    RuleErrorBuilder::message(sprintf(
                        '[%s] %s::%s() is called inside a loop: it queries the database for one product per call. '
                        . 'Collect the IDs first; getChildrenIds() and getParentIdsByChild() accept arrays.',
                        self::ID,
                        $class,
                        $node->name->toString(),
                    ))->identifier(self::IDENTIFIER)->build(),
                ];
            }
        }

        return [];
    }
}
