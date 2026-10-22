<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan\Rule;

use MagentoPerfRules\PhpStan\Rule\RecurringSetupRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RecurringSetupRule>
 */
final class RecurringSetupRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RecurringSetupRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/recurring-setup.php'], [[
            '[X-SETUP-01] Recurring::install() does heavy work (loops, loading, saving or reindexing); it runs on every setup:upgrade. Move one-time work to a data or schema patch.',
            17,
        ]]);
    }
}
