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
        return new RecurringSetupRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $message = static fn (string $class, string $method): string => sprintf(
            '[X-SETUP-01] %s::install() does heavy work (%s()); it runs on every setup:upgrade. Move one-time work to a data or schema patch.',
            $class,
            $method,
        );

        $this->analyse([__DIR__ . '/../Fixtures/recurring-setup.php'], [
            [$message('Recurring', 'reindexAll'), 17],
            // A save() inside a loop is heavy; the loop over table names in Acme\Other\Setup\Recurring is not.
            [$message('RecurringData', 'save'), 91],
            // Reads are not heavy work: getItems(), getSize() and load() in the same method are not reported.
            [$message('RecurringData', 'save'), 96],
        ]);
    }
}
