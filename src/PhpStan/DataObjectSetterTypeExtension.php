<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\Type;

/**
 * Types the magic setters of subclasses of Magento\Framework\DataObject: its __call() handles
 * set*() and uns*() and returns the object itself, so $model->setStatus(1)->save() keeps the
 * model's type. A method the class declares or documents keeps its own signature.
 */
final class DataObjectSetterTypeExtension implements ExpressionTypeResolverExtension
{
    private const DATA_OBJECT = 'Magento\Framework\DataObject';

    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
    }

    public function getType(Expr $expr, Scope $scope): ?Type
    {
        if (!$expr instanceof Expr\MethodCall
            || !$expr->name instanceof Node\Identifier
            // DataObject::__call() compares the first three letters as they are.
            || !in_array(substr($expr->name->name, 0, 3), ['set', 'uns'], true)
        ) {
            return null;
        }
        $type = $scope->getType($expr->var);
        $classes = $type->getObjectClassNames();
        if (count($classes) !== 1 || !$this->reflectionProvider->hasClass($classes[0])) {
            return null;
        }
        $class = $this->reflectionProvider->getClass($classes[0]);
        if (!$class->isSubclassOf(self::DATA_OBJECT)
            || $class->hasMethod($expr->name->name)
        ) {
            return null;
        }

        return $type;
    }
}
