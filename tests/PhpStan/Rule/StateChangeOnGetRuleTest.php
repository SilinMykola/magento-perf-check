<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\StateChangeOnGetRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<StateChangeOnGetRule>
 */
final class StateChangeOnGetRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new StateChangeOnGetRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/fpc.php'], [
            ['[P-FPC-06] Acme\\Promo\\Controller\\Promo\\Add handles GET only but calls Magento\\Checkout\\Model\\Cart::addProduct(). Change state only on POST or PUT: GET responses can be cached and served without running this code.', 89],
            ['[P-FPC-06] Acme\\Promo\\Controller\\Promo\\Add handles GET only but calls Magento\\Catalog\\Api\\ProductRepositoryInterface::save(). Change state only on POST or PUT: GET responses can be cached and served without running this code.', 90],
            ['[P-FPC-06] Acme\\Promo\\Controller\\Wishlist\\Touch handles GET only but calls Magento\\Catalog\\Api\\ProductRepositoryInterface::save(). Change state only on POST or PUT: GET responses can be cached and served without running this code.', 345],
            ['[P-FPC-06] Acme\\Promo\\Controller\\Wishlist\\Show handles GET only but calls Magento\\Catalog\\Api\\ProductRepositoryInterface::save(). Change state only on POST or PUT: GET responses can be cached and served without running this code.', 396],
        ]);
    }
}
