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

    public function testBoxFixedByCssIsLeftOut(): void
    {
        // Sized by a style attribute, or by a rule in the template's own <style> on one of the image's classes.
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/frontend/templates/image-css.phtml'], [
            [self::MESSAGE, 12],
            [self::MESSAGE, 13],
            [self::MESSAGE, 15],
            [self::MESSAGE, 16],
            [self::MESSAGE, 17],
            [self::MESSAGE, 18],
        ]);
    }

    public function testCssThatDoesNotAlwaysFixTheBoxCounts(): void
    {
        // Only .ok, .impok, the width attribute with an aspect ratio and the style attribute after the overridden .casc fix the box.
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/frontend/templates/image-css-edges.phtml'], [
            [self::MESSAGE, 14],
            [self::MESSAGE, 15],
            [self::MESSAGE, 16],
            [self::MESSAGE, 17],
            [self::MESSAGE, 18],
            [self::MESSAGE, 19],
            [self::MESSAGE, 20],
            [self::MESSAGE, 21],
            [self::MESSAGE, 28],
            // A pseudo-class before the image's compound counts as little as on it.
            [self::MESSAGE, 30],
        ]);
        // A rule for every image counts, also when it resets the size.
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/frontend/templates/image-css-reset.phtml'], [[self::MESSAGE, 2]]);
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/frontend/templates/image-css-img.phtml'], []);
    }

    public function testStylesheetsOfTheModuleAndTheThemeCount(): void
    {
        // module.css sizes .box .hero, _module.less sizes .card img.avatar behind a guard; .wide only in @media, .plain
        // only in a .min.css, and the last .hero is not inside a .box.
        $this->analyse([__DIR__ . '/../Fixtures/styled-module/view/frontend/templates/styled.phtml'], [[self::MESSAGE, 3], [self::MESSAGE, 4], [self::MESSAGE, 5]]);
        // A Hyvä template gets the module's CSS, not its LESS, which a Hyvä theme does not compile; its <template>
        // element leaves the elements around the images known.
        $this->analyse([__DIR__ . '/../Fixtures/styled-module/view/frontend/templates/hyva/styled.phtml'], [[self::MESSAGE, 2]]);
        // PHP branches open one of two wrappers: the elements around the image are not known, so .box .hero does not count.
        $this->analyse([__DIR__ . '/../Fixtures/styled-module/view/frontend/templates/branches.phtml'], [[self::MESSAGE, 2]]);
        // <img-zoom> is no image; BEM "&__thumb", a rule after "url( //...)" on one line and view/base count.
        $this->analyse([__DIR__ . '/../Fixtures/styled-module/view/frontend/templates/elements.phtml'], [[self::MESSAGE, 2]]);
        // The theme's module folder sizes .header .logo, the theme itself .badge.
        $this->analyse([__DIR__ . '/../Fixtures/app/design/frontend/Acme/shop/Acme_Shop/templates/logo.phtml'], [[self::MESSAGE, 3]]);
    }

    public function testAdminTemplateIsLeftOut(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/adminhtml/templates/admin.phtml'], []);
    }

    public function testEmailTemplateIsLeftOut(): void
    {
        // A template under templates/email, of a module or of a theme, renders an email, where layout shift does not apply.
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/frontend/templates/email/order/items.phtml'], []);
        $this->analyse([__DIR__ . '/../Fixtures/app/design/frontend/Acme/shop/Magento_Sales/templates/email/items.phtml'], []);
        // A storefront template named after email is still a page.
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/frontend/templates/newsletter/email.phtml'], [[self::MESSAGE, 2]]);
    }

    public function testAtRulesAreLeftOutAtAPcreLimit(): void
    {
        // An at-rule this long exhausts the PCRE JIT stack of the pattern that removes at-rules.
        $css = '@media print { ' . str_repeat('.x { color: red } ', 60000) . '.pr img { width: 10px; height: 10px } } .d { height: 1px }';
        $rules = (new \ReflectionMethod(ImageWithoutDimensionsRule::class, 'cssRules'))->invoke(null, $css);
        self::assertIsArray($rules);
        self::assertSame([['.d']], array_column($rules, 'selectors'));
    }
}
