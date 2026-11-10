<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\FileTypeMapper;

/**
 * For P-API-01: the constructor parameters of a class that a web API request could not
 * fill (WebapiParameterType) and that the class has a getter for. The rule decides on the
 * collected data whether the class is built for a web API input type.
 *
 * @implements Collector<InClassNode, array{class: string, params: list<array{name: string, line: int, type: string}>}>
 */
final class WebapiConstructorCollector implements Collector
{
    public function __construct(
        private readonly FileTypeMapper $fileTypeMapper,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): ?array
    {
        $class = $node->getClassReflection();
        if (!$class->isClass() || $class->isAnonymous() || !$class->hasConstructor()) {
            return null;
        }
        $constructor = $class->getNativeReflection()->getConstructor();
        if ($constructor === null) {
            return null;
        }
        // Lines of the class's own constructor; an inherited one is reported at the class.
        $lines = [];
        foreach ($node->getOriginalNode()->getMethod('__construct')->params ?? [] as $param) {
            if ($param->var instanceof Variable && is_string($param->var->name)) {
                $lines[$param->var->name] = $param->getStartLine();
            }
        }
        $docComment = (string) $constructor->getDocComment();
        // TypeProcessor resolves a doc type against the imports of the parameter's declaring class.
        $declaring = $constructor->getDeclaringClass();
        $file = $declaring->getFileName();
        $resolveClass = function (string $name) use ($declaring, $file): ?string {
            if ($file === false) {
                return null;
            }
            $phpDoc = $this->fileTypeMapper->getResolvedPhpDoc($file, $declaring->getName(), null, '__construct', '/** @var ' . $name . ' */');
            foreach ($phpDoc->getVarTags() as $tag) {
                foreach ($tag->getType()->getObjectClassNames() as $resolved) {
                    // PHPStan turns a @template or a type alias into its bound class; the core only knows classes.
                    $sameName = strcasecmp(substr(strrchr('\\' . $resolved, '\\') ?: '', 1), substr(strrchr('\\' . $name, '\\') ?: '', 1)) === 0;

                    return $sameName && $this->reflectionProvider->hasClass($resolved) ? $resolved : null;
                }
            }

            return null;
        };

        $params = [];
        foreach ($constructor->getParameters() as $parameter) {
            $field = str_replace('_', '', ucwords($parameter->getName(), '_'));
            // The request only sends fields its type has a getter for; the rule checks the exact name on that type.
            if (!$class->hasNativeMethod('get' . $field) && !$class->hasNativeMethod('is' . $field)) {
                continue;
            }
            $type = WebapiParameterType::of($parameter, $docComment, $resolveClass);
            if ($type === null || WebapiParameterType::isSupported($type)) {
                continue;
            }
            $params[] = ['name' => $parameter->getName(), 'line' => $lines[$parameter->getName()] ?? $node->getStartLine(), 'type' => $type];
        }

        return $params === [] ? null : ['class' => $class->getName(), 'params' => $params];
    }
}
