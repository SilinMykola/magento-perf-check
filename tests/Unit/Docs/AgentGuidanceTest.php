<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Docs;

use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\Cli\Options;

/**
 * The skill and the AGENTS.md snippet must be loadable and show only options the command accepts.
 */
final class AgentGuidanceTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../..';

    private const SKILL = 'resources/skill/magento-perf-check/SKILL.md';

    public function testSkillHasFrontMatter(): void
    {
        $skill = (string) file_get_contents(self::ROOT . '/' . self::SKILL);

        self::assertSame(1, preg_match('/\A---\n(.*?)\n---\n/s', $skill, $m), 'SKILL.md must start with front matter');
        self::assertMatchesRegularExpression('/^name: magento-perf-check$/m', $m[1]);
        self::assertMatchesRegularExpression('/^description: \S.{20,}$/m', $m[1]);
    }

    public function testOnlyAcceptedOptionsAreShown(): void
    {
        preg_match_all('/(?<![\w-])--[a-z][a-z-]*/', Options::usage(), $accepted);
        foreach ([self::SKILL, 'docs/agents.md'] as $file) {
            preg_match_all('/(?<![\w-])--[a-z][a-z-]*/', (string) file_get_contents(self::ROOT . '/' . $file), $shown);
            self::assertNotEmpty($shown[0], $file);
            foreach (array_unique($shown[0]) as $option) {
                self::assertContains($option, $accepted[0], $file . ' shows ' . $option . ', which the command does not accept');
            }
        }
    }
}
