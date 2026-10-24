<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Rule;

use MagentoPerfRules\Rule\RuleRegistry;
use MagentoPerfRules\Rule\RuleStatus;
use PHPUnit\Framework\TestCase;

/**
 * The README rule tables list every registered rule, each link resolves, and experimental
 * rules are listed in their own table.
 */
final class ReadmeRulesTest extends TestCase
{
    public function testEveryRuleIsListedWithAWorkingLink(): void
    {
        $root = __DIR__ . '/../../..';
        $readme = (string) file_get_contents($root . '/README.md');
        preg_match_all('#\| \[([A-Z]-[A-Z]+-\d+|T-\d+)\]\((docs/rules/[^)]+\.md)\)#', $readme, $matches, PREG_SET_ORDER);

        $listed = [];
        foreach ($matches as [, $id, $link]) {
            $listed[] = $id;
            self::assertFileExists($root . '/' . $link, $id);
        }
        $registered = array_map(static fn ($rule): string => $rule->id, (new RuleRegistry())->all());
        sort($listed);
        sort($registered);

        self::assertSame($registered, $listed);
    }

    public function testExperimentalRulesHaveTheirOwnTable(): void
    {
        $readme = (string) file_get_contents(__DIR__ . '/../../../README.md');
        $start = strpos($readme, '### Experimental (off by default)');
        self::assertIsInt($start);
        $end = strpos($readme, "\n## ", $start);
        $section = substr($readme, $start, $end === false ? null : $end - $start);
        preg_match_all('#^\| \[([A-Z0-9-]+)\]\(docs/rules/#m', $section, $matches);

        $experimental = [];
        foreach ((new RuleRegistry())->all() as $rule) {
            if ($rule->status === RuleStatus::Experimental) {
                $experimental[] = $rule->id;
            }
        }
        $listed = $matches[1];
        sort($listed);
        sort($experimental);

        self::assertSame($experimental, $listed);
    }
}
