<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\AttributeTextInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<AttributeTextInLoopRule>
 */
final class AttributeTextInLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new AttributeTextInLoopRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $message = static fn (string $method): string => '[P-LOOP-06] ' . $method . '() is called inside a loop: with the table source model, the default for a select attribute, one query per call. '
            . 'Load the options once with getAllOptions() and look labels up in that array.';

        $this->analyse([__DIR__ . '/../Fixtures/catalog-in-loop.php'], [
            [$message('getAttributeText'), 76],
            [$message('getOptionText'), 77],
            [$message('getAttributeText'), 80],
            [$message('getAttributeText'), 144],
        ]);
    }
}
