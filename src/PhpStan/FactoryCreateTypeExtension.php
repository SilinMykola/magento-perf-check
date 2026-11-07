<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Types create() of a factory that Magento generates and that does not exist yet: without
 * this, findings depend on whether setup:di:compile has run. Magento builds Foo\BarFactory
 * from the name alone, so its create() returns Foo\Bar. A factory class that exists is
 * left to its own signature.
 */
final class FactoryCreateTypeExtension implements ExpressionTypeResolverExtension
{
    private const SUFFIX = 'Factory';

    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
    }

    public function getType(Expr $expr, Scope $scope): ?Type
    {
        if (!$expr instanceof Expr\MethodCall
            || !$expr->name instanceof Node\Identifier
            || $expr->name->toLowerString() !== 'create'
        ) {
            return null;
        }
        $classes = $scope->getType($expr->var)->getObjectClassNames();
        if (count($classes) !== 1 || !str_ends_with($classes[0], self::SUFFIX) || $this->reflectionProvider->hasClass($classes[0])) {
            return null;
        }
        $created = substr($classes[0], 0, -strlen(self::SUFFIX));

        return $this->reflectionProvider->hasClass($created) ? new ObjectType($created) : null;
    }
}
