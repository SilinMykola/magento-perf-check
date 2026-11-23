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
        return new NoCacheHeadersRule($this->createReflectionProvider());
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
            // The controller's own setNoCacheHeaders() is not the response: only what it does inside counts.
            ['[P-FPC-05] setHeader() in Acme\\Promo\\Controller\\Feed\\OwnHelper makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 564],
            // setHeader() with a cache header counts on any receiver.
            ['[P-FPC-05] setHeader() in Acme\\Promo\\Controller\\Feed\\OwnHelper makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 569],
            // A computed value counts unless its literal parts ask for caching; a block's setHeader() is no header.
            ['[P-FPC-05] setHeader() in Acme\\Promo\\Controller\\Headers\\Computed makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 864],
            ['[P-FPC-05] setHeader() in Acme\\Promo\\Controller\\Headers\\Computed makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 865],
            ['[P-FPC-05] setHeader() in Acme\\Promo\\Controller\\Headers\\Computed makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 866],
            // The computed part of a public value must be a number.
            ['[P-FPC-05] setHeader() in Acme\\Promo\\Controller\\Headers\\Interpolated makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 891],
            ['[P-FPC-05] setHeader() in Acme\\Promo\\Controller\\Headers\\Interpolated makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 892],
            ['[P-FPC-05] setHeader() in Acme\\Promo\\Controller\\Headers\\Interpolated makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.', 893],
        ]);
    }
}
