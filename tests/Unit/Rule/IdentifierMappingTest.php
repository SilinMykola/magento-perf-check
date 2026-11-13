<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Rule;

use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\RuleStatus;
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
        foreach ($this->ruleClasses('extension.neon') as $class) {
            foreach ($this->identifiers($class) as $name => $value) {
                if ($registry->findByIdentifier($value) === null) {
                    $unmapped[] = $class . '::' . $name . ' = ' . $value;
                }
            }
        }

        self::assertSame([], $unmapped);
    }

    public function testRulesAreLoadedFromTheFileOfTheirStatus(): void
    {
        $loaded = [];
        foreach (['extension.neon', 'extension-experimental.neon'] as $file) {
            $loaded[$file] = [];
            foreach ($this->ruleClasses($file) as $class) {
                array_push($loaded[$file], ...array_values($this->identifiers($class)));
            }
        }

        foreach ((new RuleRegistry())->all() as $rule) {
            // Upgrade rules need the command's version gate, which PHPStan alone lacks, so they stay out of extension.neon.
            $file = $rule->status === RuleStatus::Experimental || $rule->isUpgradeRule() ? 'extension-experimental.neon' : 'extension.neon';
            foreach (array_keys($rule->identifiers) as $identifier) {
                self::assertContains($identifier, $loaded[$file], $rule->id);
            }
        }
    }

    /**
     * @param class-string $class
     * @return array<string, string> constant name => identifier
     */
    private function identifiers(string $class): array
    {
        $identifiers = [];
        foreach ((new ReflectionClass($class))->getReflectionConstants() as $constant) {
            $value = $constant->getValue();
            if (str_starts_with($constant->getName(), 'IDENTIFIER') && is_string($value)) {
                $identifiers[$constant->getName()] = $value;
            }
        }

        return $identifiers;
    }

    /**
     * @return list<class-string>
     */
    private function ruleClasses(string $file): array
    {
        $config = Neon::decodeFile(__DIR__ . '/../../../' . $file);
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
