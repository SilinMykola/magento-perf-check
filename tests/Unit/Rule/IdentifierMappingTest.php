<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Rule;

use MagentoPerfRules\Rule\RuleRegistry;
use Nette\Neon\Neon;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Every identifier a registered PHPStan rule declares (constants named IDENTIFIER*)
 * must map to a rule in the registry, or its findings would get no rule and severity.
 */
final class IdentifierMappingTest extends TestCase
{
    public function testEveryRuleIdentifierIsMapped(): void
    {
        $registry = new RuleRegistry();
        $unmapped = [];
        foreach ($this->ruleClasses() as $class) {
            foreach ((new ReflectionClass($class))->getReflectionConstants() as $constant) {
                $value = $constant->getValue();
                if (str_starts_with($constant->getName(), 'IDENTIFIER') && is_string($value)) {
                    if ($registry->findByIdentifier($value) === null) {
                        $unmapped[] = $class . '::' . $constant->getName() . ' = ' . $value;
                    }
                }
            }
        }

        self::assertSame([], $unmapped);
    }

    /**
     * @return list<class-string>
     */
    private function ruleClasses(): array
    {
        $config = Neon::decodeFile(__DIR__ . '/../../../extension.neon');
        self::assertIsArray($config);
        $classes = [];
        foreach ($config['rules'] ?? [] as $class) {
            $classes[] = $class;
        }
        foreach ($config['services'] ?? [] as $service) {
            if (is_array($service) && in_array('phpstan.rules.rule', $service['tags'] ?? [], true)) {
                $classes[] = $service['class'];
            }
        }
        self::assertNotSame([], $classes);

        /** @var list<class-string> $classes */
        return $classes;
    }
}
