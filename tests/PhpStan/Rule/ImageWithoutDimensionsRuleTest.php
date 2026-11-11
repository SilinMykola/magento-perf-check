<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\ImageWithoutDimensionsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ImageWithoutDimensionsRule>
 */
final class ImageWithoutDimensionsRuleTest extends RuleTestCase
{
    private const MESSAGE = '[T-06] This <img> has no width and height: the browser cannot reserve its space before it loads, so the page shifts when it arrives. Set both attributes to the image\'s size.';

    protected function getRule(): Rule
    {
        return new ImageWithoutDimensionsRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testImagesWithoutBothDimensions(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/frontend/templates/image.phtml'], [
            [self::MESSAGE, 5],
            [self::MESSAGE, 6],
            [self::MESSAGE, 11],
            [self::MESSAGE, 17],
            [self::MESSAGE, 18],
            [self::MESSAGE, 19],
        ]);
    }

    public function testAdminTemplateIsLeftOut(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/adminhtml/templates/admin.phtml'], []);
    }
}
