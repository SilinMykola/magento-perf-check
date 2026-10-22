<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan\Rule;

use MagentoPerfRules\PhpStan\ProjectIndexProvider;
use MagentoPerfRules\PhpStan\Rule\AroundPluginRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<AroundPluginRule>
 */
final class AroundPluginRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new AroundPluginRule(self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testFixture(): void
    {
        $suffix = ' once, unconditionally and with unchanged arguments. A before and/or after plugin does the same with less overhead.';

        $this->analyse([__DIR__ . '/../Fixtures/around-plugin.php'], [
            ['[P-PLG-01] Acme\Promo\Plugin\PricePlugin::aroundGetPrice() calls $proceed' . $suffix, 10],
            ['[P-PLG-01] Acme\Promo\Plugin\PricePlugin::aroundGetFinalPrice() calls $proceed' . $suffix, 17],
            ['[P-PLG-01] Acme\Promo\Model\PriceModifier::aroundGetPrice() calls $proceed' . $suffix, 69],
        ]);
    }
}
