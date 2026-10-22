<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Rule;

use MagentoPerfRules\Rule\RuleRegistry;
use PHPUnit\Framework\TestCase;

/**
 * The README rule table lists every registered rule, and each link resolves.
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
}
