<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\RecurringSetupRule;
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
        $message = 'install() does heavy work (loading, saving or reindexing); it runs on every setup:upgrade. Move one-time work to a data or schema patch.';

        $this->analyse([__DIR__ . '/../Fixtures/recurring-setup.php'], [
            ['[X-SETUP-01] Recurring::' . $message, 17],
            // A save() inside a loop is heavy; the loop over table names in Acme\Other\Setup\Recurring is not.
            ['[X-SETUP-01] RecurringData::' . $message, 83],
        ]);
    }
}
