<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\NoCacheHeadersRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<NoCacheHeadersRule>
 */
final class NoCacheHeadersRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new NoCacheHeadersRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/fpc.php'], [
            ['[P-FPC-05] setNoCacheHeaders() in Acme\\Promo\\Controller\\Promo\\Add makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 88],
            ['[P-FPC-05] setHeader() in Acme\\Promo\\Controller\\Promo\\Legacy makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 134],
            ['[P-FPC-05] setHeader() in Acme\\Promo\\Model\\Resolver\\Banners makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 173],
            ['[P-FPC-05] setHeader() in Acme\Promo\Controller\Download\Link makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 288],
        ]);
    }
}
