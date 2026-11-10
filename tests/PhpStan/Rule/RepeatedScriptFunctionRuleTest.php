<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\RepeatedScriptFunctionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RepeatedScriptFunctionRule>
 */
final class RepeatedScriptFunctionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RepeatedScriptFunctionRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    private const SUFFIX = '() is declared in a part of the template that is rendered once per item: every item sends and parses it again. Declare it once, in a template rendered once per page.';

    public function testFunctionInATemplateLoop(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/templates/repeated-script.phtml'], [
            ['[T-03] The JavaScript function initPerItem' . self::SUFFIX, 13],
        ]);
    }

    public function testFunctionInTheProductListItemTemplate(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/templates/Magento_Catalog/templates/product/list/item.phtml'], [
            ['[T-03] The JavaScript function initWishlist' . self::SUFFIX, 10],
        ]);
    }
}
