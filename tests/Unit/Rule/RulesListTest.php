<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Rule;

use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\RuleStatus;
use PHPUnit\Framework\TestCase;

/**
 * docs/rules.md lists every registered rule in the table matching its status, with its
 * default severity, and every link resolves.
 */
final class RulesListTest extends TestCase
{
    private const DOCS = __DIR__ . '/../../../docs';

    public function testEveryRuleIsListedWithAWorkingLink(): void
    {
        $listed = [];
        foreach ([...$this->table('## Default set'), ...$this->table('## Experimental (off by default)')] as $id => [$link]) {
            $listed[] = $id;
            self::assertFileExists(self::DOCS . '/' . $link, $id);
        }
        $registered = array_map(static fn ($rule): string => $rule->id, (new RuleRegistry())->all());
        sort($listed);
        sort($registered);

        self::assertSame($registered, $listed);
    }

    public function testRulesAreInTheTableOfTheirStatus(): void
    {
        $default = array_keys($this->table('## Default set'));
        $experimental = array_keys($this->table('## Experimental (off by default)'));
        foreach ((new RuleRegistry())->all() as $rule) {
            self::assertContains($rule->id, $rule->status === RuleStatus::Experimental ? $experimental : $default, $rule->id);
        }
    }

    public function testSeverityColumnNamesTheDefaultSeverity(): void
    {
        $rows = [...$this->table('## Default set'), ...$this->table('## Experimental (off by default)')];
        foreach ((new RuleRegistry())->all() as $rule) {
            self::assertArrayHasKey($rule->id, $rows);
            self::assertMatchesRegularExpression('/\b' . $rule->severity->value . '\b/', $rows[$rule->id][1], $rule->id);
        }
    }

    /**
     * Rows of the table under a heading: rule ID => [link, severity cell].
     *
     * @return array<string, array{string, string}>
     */
    private function table(string $heading): array
    {
        $document = (string) file_get_contents(self::DOCS . '/rules.md');
        $start = strpos($document, "\n" . $heading . "\n");
        self::assertIsInt($start, $heading . ' is missing in docs/rules.md');
        $end = strpos($document, "\n## ", $start + 1);
        $section = substr($document, $start, $end === false ? null : $end - $start);
        preg_match_all('#^\| \[([A-Z0-9-]+)\]\((rules/[^)]+\.md)\) \| [^|]+ \| ([^|]+) \|#m', $section, $matches, PREG_SET_ORDER);

        $rows = [];
        foreach ($matches as [, $id, $link, $severity]) {
            $rows[$id] = [$link, $severity];
        }

        return $rows;
    }
}
