<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use ReflectionNamedType;
use ReflectionParameter;

/**
 * The type Magento's TypeProcessor::getParamType() gives a constructor parameter, and whether
 * ServiceInputProcessor then passes it a request value (magento/magento2@a385d02f,
 * ServiceInputProcessor.php#L290-L296). Laminas ParameterReflection::detectType() comes first:
 * a builtin native type, a native class, else the first type of the parameter's @param tag.
 */
final class WebapiParameterType
{
    /** Laminas ParamTag::initialize(), as written there. */
    private const PARAM_TAG = '#((?:[\w|\\\]+(?:\[\])*\|?)+)(?:\s+(\$\S+))?(?:\s+(.*))?#s';

    private const API_DATA = '~\\\\?\w+\\\\\w+\\\\Api\\\\Data\\\\~';

    /** TypeProcessor::normalizeType(). */
    private const NORMALIZED = ['str' => 'string', 'integer' => 'int', 'bool' => 'boolean', 'mixed' => 'anyType'];

    private const SIMPLE = ['string', 'int', 'float', 'double', 'boolean'];

    /**
     * @param callable(string): ?string $resolveClass a relative class name, resolved against the declaring class's file, or null when no such class exists
     * @return string|null null when the core fails on the parameter before it gets to the type check
     */
    public static function of(ReflectionParameter $parameter, string $docComment, callable $resolveClass): ?string
    {
        $native = $parameter->getType();
        $tags = self::paramTags($docComment);
        if ($native !== null && !$native instanceof ReflectionNamedType) {
            return null;
        }
        if ($native !== null && !$native->isBuiltin()) {
            return $native->getName() === 'self' ? $parameter->getDeclaringClass()?->getName() : $native->getName();
        }
        $type = $native?->getName() ?? self::docType($tags, $parameter->getPosition(), '$' . $parameter->getName());
        // getParamType() throws on a first doc type of null.
        if ($type === 'null') {
            return null;
        }
        if ($type === 'array') {
            // TypeProcessor takes the @param tag at the parameter's position, whatever its name.
            $first = $tags[$parameter->getPosition()]['types'][0] ?? null;
            if ($first === null) {
                return null;
            }
            $type = self::resolve($first, $resolveClass);

            return str_contains($type, '[]') ? $type : $type . '[]';
        }

        return self::resolve($type ?? '', $resolveClass);
    }

    public static function isSupported(string $type): bool
    {
        $type = self::NORMALIZED[$type] ?? $type;
        if (preg_match('/(\[\]$|^ArrayOf)/', $type) === 1) {
            $item = str_replace('[]', '', $type);
            $type = self::NORMALIZED[$item] ?? $item;
        }

        return in_array($type, self::SIMPLE, true) || preg_match(self::API_DATA, $type) === 1;
    }

    /**
     * @return list<array{types: list<string>, variable: ?string}> each @param tag, in order
     */
    private static function paramTags(string $docComment): array
    {
        $tags = [];
        // Laminas reads a tag only at the start of a doc comment line.
        preg_match_all('/^[ \t]*(?:\/\*\*)?[ \t]*\*?[ \t]*@param(?=[ \t]|$)[ \t]*(.*?)[ \t]*(?:\*\/)?$/m', $docComment, $matches);
        foreach ($matches[1] as $content) {
            $tags[] = preg_match(self::PARAM_TAG, $content, $tag) === 1
                ? ['types' => explode('|', $tag[1]), 'variable' => ($tag[2] ?? '') !== '' ? $tag[2] : null]
                : ['types' => [], 'variable' => null];
        }

        return $tags;
    }

    /**
     * ParameterReflection::detectType() for a parameter without a native type: the tag at its
     * position when it names the parameter, else the first tag that names it. Laminas keeps a
     * missing variable name as null, so a tag without one never matches.
     *
     * @param list<array{types: list<string>, variable: ?string}> $tags
     */
    private static function docType(array $tags, int $position, string $variable): ?string
    {
        $tag = $tags[$position] ?? null;
        if ($tag !== null && $tag['variable'] === $variable) {
            return $tag['types'][0] ?? '';
        }
        foreach ($tags as $tag) {
            if ($tag['variable'] === $variable) {
                return $tag['types'][0] ?? '';
            }
        }

        return null;
    }

    /**
     * TypeProcessor::resolveFullyQualifiedClassName(): a lower-case name is a simple type, a name
     * that starts with a backslash is kept, another one is resolved or stays as written.
     *
     * @param callable(string): ?string $resolveClass
     */
    private static function resolve(string $type, callable $resolveClass): string
    {
        $type = trim($type);
        if (strtolower($type) === $type || str_starts_with($type, '\\')) {
            return $type;
        }
        $class = $resolveClass(strstr($type, '[', true) ?: $type);

        return $class !== null ? '\\' . $class . (str_ends_with($type, '[]') ? '[]' : '') : $type;
    }
}
