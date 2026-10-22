<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan;

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
    public const RESOURCE_MODEL = 'Magento\Framework\Model\ResourceModel\Db\AbstractDb';

    private const REPOSITORY_SUFFIX = 'RepositoryInterface';

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

    public static function implements(?ClassReflection $class, string $interface): bool
    {
        return $class !== null && $class->implementsInterface($interface);
    }

    public static function extends(?ClassReflection $class, string $parent): bool
    {
        return $class !== null && ($class->getName() === $parent || $class->isSubclassOf($parent));
    }
}
