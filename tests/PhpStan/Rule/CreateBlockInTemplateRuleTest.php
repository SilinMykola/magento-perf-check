<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\CreateBlockInTemplateRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CreateBlockInTemplateRule>
 */
final class CreateBlockInTemplateRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CreateBlockInTemplateRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testTemplateOnly(): void
    {
        $this->analyse(
            [__DIR__ . '/../Fixtures/templates/promo.phtml', __DIR__ . '/../Fixtures/create-block-in-class.php'],
            [[
                '[T-01] The template creates a block with getLayout()->createBlock(). The Technical guidelines forbid this; declare the block in layout XML and render it with getChildHtml(), or use a view model.',
                5,
            ]],
        );
    }
}
