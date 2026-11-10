<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use ReflectionMethod;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\WebapiConstructorCollector;

/**
 * P-API-01: since APSB25-88 a web API request passes no value to a constructor parameter of a
 * data object unless its type is simple or an Api\Data type; any other field needs a setter on
 * the requested type (magento/magento2@a385d02f, ServiceInputProcessor.php#L229-L398). A field
 * with a getter, an unsupported constructor parameter and no setter cannot be sent at all.
 *
 * Runs on collected data, once per analysis: the input types depend on files PHPStan's result
 * cache does not link to the class (service interfaces, webapi.xml).
 *
 * @implements Rule<CollectedDataNode>
 */
final class WebapiConstructorParameterRule implements Rule
{
    public const ID = 'P-API-01';
    public const IDENTIFIER = 'magentoPerf.webapiConstructorParameter';

    public function __construct(
        private readonly ProjectIndexProvider $index,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return CollectedDataNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $collected = $node->get(WebapiConstructorCollector::class);
        if ($collected === []) {
            return [];
        }
        // Without Magento configuration there are no routes, so nothing is reported.
        $index = $this->index->get();

        // Class built => the input types it is built for.
        $built = [];
        foreach ($this->inputTypes($index->webapiMethods()) as $key => $type) {
            foreach (self::builtClasses($key, $index->webapiPreferences()) as $class) {
                $built[$class][] = $type;
            }
        }

        $errors = [];
        foreach ($collected as $file => $entries) {
            foreach ($entries as $entry) {
                foreach ($entry['params'] as $param) {
                    $field = str_replace('_', '', ucwords($param['name'], '_'));
                    foreach ($built[strtolower($entry['class'])] ?? [] as $type) {
                        if ((!self::hasPublicMethod($type, 'get' . $field) && !self::hasPublicMethod($type, 'is' . $field))
                            || self::hasPublicMethod($type, 'set' . $field)
                            || self::hasPublicMethod($type, 'setIs' . $field)
                        ) {
                            continue;
                        }
                        $errors[] = RuleErrorBuilder::message(sprintf(
                            '[%1$s] A web API request cannot set $%2$s of %3$s, built for %4$s: since APSB25-88 the core passes no request value '
                            . 'to a constructor parameter of type %5$s, and %4$s has no set%6$s(), so a request with this field fails with "%6$s" is not supported. '
                            . 'Add set%6$s() to %4$s, or give the parameter a simple or *\Api\Data\* type.',
                            self::ID,
                            $param['name'],
                            $entry['class'],
                            $type->getName(),
                            $param['type'] !== '' ? $param['type'] : '(none)',
                            $field,
                        ))->identifier(self::IDENTIFIER)->file($file)->line($param['line'])->build();
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Types reachable from the parameters of web API service methods, through getter return types.
     *
     * @param array<string, list<string>> $methods
     * @return array<string, ClassReflection> lower-case name => type
     */
    private function inputTypes(array $methods): array
    {
        $queue = [];
        foreach ($methods as $class => $names) {
            if (!$this->reflectionProvider->hasClass($class)) {
                continue;
            }
            $service = $this->reflectionProvider->getClass($class);
            foreach ($names as $name) {
                // A built-in method can have several variants.
                foreach ($service->hasNativeMethod($name) ? $service->getNativeMethod($name)->getVariants() : [] as $variant) {
                    foreach ($variant->getParameters() as $parameter) {
                        array_push($queue, ...$parameter->getType()->getReferencedClasses());
                    }
                }
            }
        }

        $types = [];
        while ($queue !== []) {
            $name = array_pop($queue);
            $key = strtolower($name);
            if (isset($types[$key]) || !$this->reflectionProvider->hasClass($name)) {
                continue;
            }
            $type = $this->reflectionProvider->getClass($name);
            $types[$key] = $type;
            foreach ($type->getNativeReflection()->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (preg_match('/^(?:get|is)[A-Z]/', $method->getName()) !== 1 || $method->getNumberOfRequiredParameters() > 0) {
                    continue;
                }
                foreach ($type->getNativeMethod($method->getName())->getVariants() as $variant) {
                    array_push($queue, ...$variant->getReturnType()->getReferencedClasses());
                }
            }
        }

        return $types;
    }

    /**
     * ObjectManager\Config\Config::getPreference(): preferences followed to the end of the chain,
     * or the type itself. Several ends when modules configure different preferences.
     *
     * @param array<string, list<string>> $preferences
     * @return list<string>
     */
    private static function builtClasses(string $type, array $preferences): array
    {
        $ends = [];
        $queue = [[$type, [$type => true]]];
        while ($queue !== []) {
            [$current, $seen] = array_pop($queue);
            if (!isset($preferences[$current])) {
                $ends[$current] = true;
                continue;
            }
            foreach ($preferences[$current] as $next) {
                if (!isset($seen[$next])) {
                    $queue[] = [$next, $seen + [$next => true]];
                }
            }
        }

        return array_keys($ends);
    }

    /** NameFinder::hasMethod(): the name must match exactly. */
    private static function hasPublicMethod(ClassReflection $type, string $name): bool
    {
        $native = $type->getNativeReflection();
        if (!$native->hasMethod($name)) {
            return false;
        }
        $method = $native->getMethod($name);

        return $method->getName() === $name && $method->isPublic();
    }
}
