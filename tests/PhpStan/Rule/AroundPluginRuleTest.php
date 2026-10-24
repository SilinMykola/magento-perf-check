<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\AroundPluginRule;
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
            ['[P-PLG-01] Acme\Promo\Plugin\PricePlugin::aroundGetPrice() calls $proceed' . $suffix, 11],
            ['[P-PLG-01] Acme\Promo\Plugin\PricePlugin::aroundGetFinalPrice() calls $proceed' . $suffix, 18],
            ['[P-PLG-01] Acme\Promo\Plugin\PricePlugin::aroundInCondition() calls $proceed' . $suffix, 85],
            ['[P-PLG-01] Acme\Promo\Plugin\PricePlugin::aroundResultIntoArgument() calls $proceed' . $suffix, 97],
            ['[P-PLG-01] Acme\Promo\Model\PriceModifier::aroundGetPrice() calls $proceed' . $suffix, 121],
        ]);
    }
}
