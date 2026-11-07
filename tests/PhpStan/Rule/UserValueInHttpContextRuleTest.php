<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\UserValueInHttpContextRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UserValueInHttpContextRule>
 */
final class UserValueInHttpContextRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new UserValueInHttpContextRule();
    }

    public function testFixture(): void
    {
        $message = '[P-FPC-03] A value specific to a visitor or a moment is added to the HTTP context: the page cache keeps one copy per value. '
            . 'Use a value shared by many visitors, such as a customer group, and load personal data through customer sections.';

        $this->analyse(
            [__DIR__ . '/../Fixtures/http-context.php'],
            array_map(static fn (int $line): array => [$message, $line], range(22, 36)),
        );
    }
}
