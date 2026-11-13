<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Rule;

use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\Config\ConfigLoader;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\RuleStatus;

/**
 * resources/all-rules.neon, which calibration reports run with, enables every experimental rule.
 */
final class AllRulesConfigTest extends TestCase
{
    public function testEnablesEveryRule(): void
    {
        $registry = new RuleRegistry();
        $config = (new ConfigLoader($registry))->load(__DIR__ . '/../../../resources/all-rules.neon');

        $off = [];
        $experimental = 0;
        foreach ($registry->all() as $rule) {
            if ($rule->status === RuleStatus::Experimental) {
                ++$experimental;
            }
            if (!$config->isEnabled($rule->id, $registry)) {
                $off[] = $rule->id;
            }
        }

        self::assertGreaterThan(0, $experimental);
        self::assertSame([], $off);
    }
}
