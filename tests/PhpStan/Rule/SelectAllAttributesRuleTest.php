<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\SelectAllAttributesRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<SelectAllAttributesRule>
 */
final class SelectAllAttributesRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SelectAllAttributesRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/catalog-in-loop.php'], [
            ["[P-COL-03] addAttributeToSelect('*') reads the values of every attribute of the entity. Select the attributes this code uses.", 100],
        ]);
    }
}
