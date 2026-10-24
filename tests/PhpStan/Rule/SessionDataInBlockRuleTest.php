<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\SessionDataInBlockRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<SessionDataInBlockRule>
 */
final class SessionDataInBlockRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SessionDataInBlockRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/fpc.php'], [
            ['[P-FPC-02] Acme\\Promo\\Block\\PromoBlock reads Magento\\Customer\\Model\\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.', 21],
            ['[P-FPC-02] Acme\\Promo\\ViewModel\\MiniCart reads Magento\\Checkout\\Model\\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.', 44],
        ]);
    }
}
