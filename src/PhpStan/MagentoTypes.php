<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Type questions several rules ask about Magento classes.
 */
final class MagentoTypes
{
    public const ABSTRACT_MODEL = 'Magento\Framework\Model\AbstractModel';
    public const DB_COLLECTION = 'Magento\Framework\Data\Collection\AbstractDb';
    private const RESOURCE_MODEL = 'Magento\Framework\Model\ResourceModel\Db\AbstractDb';
    private const EAV_RESOURCE_MODEL = 'Magento\Eav\Model\Entity\AbstractEntity';

    private const REPOSITORY_SUFFIX = 'RepositoryInterface';
    private const DATA_OBJECT = 'Magento\Framework\DataObject';
    public const PRODUCT_MODEL = 'Magento\Catalog\Model\Product';
    private const PRODUCT = 'Magento\Catalog\Api\Data\ProductInterface';
    private const PRODUCT_COLLECTION = 'Magento\Catalog\Model\ResourceModel\Product\Collection';
    private const SEARCH_CRITERIA = 'Magento\Framework\Api\SearchCriteriaInterface';

    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
    }

    /**
     * Name of the first class of $type that is a subtype of $class, or null when PHPStan
     * cannot prove it (unknown types never match).
     */
    public function subtypeOf(Type $type, string $class): ?string
    {
        return self::isObjectOf($type, $class) ? $type->getObjectClassNames()[0] : null;
    }

    /**
     * True when $type is an object of $class. A type without object classes never
     * matches: this excludes "never", which PHPStan infers for impossible branches
     * (e.g. an object after is_array()) and which is formally a subtype of every class.
     */
    public static function isObjectOf(Type $type, string $class): bool
    {
        return $type->getObjectClassNames() !== [] && (new ObjectType($class))->isSuperTypeOf($type)->yes();
    }

    /**
     * True when the receiver of $call is a product, also when a repository returned it as the
     * interface, which does not declare the costly methods. Collections have no item type of their own, so
     * the loop variable of "foreach ($productCollection as $product)" is mixed or a DataObject:
     * only then the iterated collection tells what it is.
     */
    public function isProduct(MethodCall $call, Scope $scope, string $class = self::PRODUCT): bool
    {
        $type = $scope->getType($call->var);
        $classes = $type->getObjectClassNames();
        if ($classes !== [] && $classes !== [self::DATA_OBJECT]) {
            return $this->subtypeOf($type, $class) !== null;
        }
        $iterated = $call->getAttribute(LoopContextVisitor::ITERATED_EXPRESSION);
        if ($iterated instanceof MethodCall && $iterated->name instanceof Node\Identifier && $iterated->name->toLowerString() === 'getitems') {
            $iterated = $iterated->var;
        }

        return $iterated instanceof Node\Expr
            && $this->subtypeOf($scope->getType($iterated), self::PRODUCT_COLLECTION) !== null;
    }

    /** The class of $type when it is a flat or an EAV resource model, such as the one of products. */
    public function resourceModel(Type $type): ?string
    {
        return $this->subtypeOf($type, self::RESOURCE_MODEL) ?? $this->subtypeOf($type, self::EAV_RESOURCE_MODEL);
    }

    /**
     * The first class of $type that is, or implements, an interface named *RepositoryInterface.
     */
    public function repository(Type $type): ?string
    {
        foreach ($type->getObjectClassNames() as $className) {
            if (!$this->reflectionProvider->hasClass($className)) {
                continue;
            }
            $reflection = $this->reflectionProvider->getClass($className);
            $names = array_keys($reflection->getInterfaces());
            if ($reflection->isInterface()) {
                $names[] = $reflection->getName();
            }
            foreach ($names as $name) {
                if (str_ends_with($name, self::REPOSITORY_SUFFIX)) {
                    return $reflection->getName();
                }
            }
        }

        return null;
    }

    /** True when getList() of $type takes search criteria, which means it runs a query. */
    public static function takesSearchCriteria(Type $type, Scope $scope): bool
    {
        $method = $scope->getMethodReflection($type, 'getList');
        foreach ($method?->getVariants() ?? [] as $variant) {
            $first = $variant->getParameters()[0] ?? null;
            if ($first !== null && self::isObjectOf($first->getType(), self::SEARCH_CRITERIA)) {
                return true;
            }
        }

        return false;
    }

    public static function implements(?ClassReflection $class, string $interface): bool
    {
        return $class !== null && $class->implementsInterface($interface);
    }

    public static function extends(?ClassReflection $class, string $parent): bool
    {
        return $class !== null && ($class->getName() === $parent || $class->isSubclassOf($parent));
    }
}
