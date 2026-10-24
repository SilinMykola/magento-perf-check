<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\SessionWithoutProxyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * The project index comes from tests/Fixtures/Project/indexed: Acme_Core configures a
 * Proxy for Banner::$customerSession, Acme_Promo's frontend di.xml one for Header.
 *
 * @extends RuleTestCase<SessionWithoutProxyRule>
 */
final class SessionWithoutProxyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SessionWithoutProxyRule(self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testReportsOnlyArgumentsWithoutProxy(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/session-without-proxy.php'], [
            [
                '[P-DI-01] Acme\Promo\Block\Badge receives $customerSession (Magento\Customer\Model\Session) without a Proxy: it is created together with this class, and a session starts on creation. Configure Magento\Customer\Model\Session\Proxy for this argument in di.xml; do not type-hint the Proxy.',
                26,
            ],
            [
                '[P-DI-01] Acme\Promo\Block\Badge receives $cookieManager (Magento\Framework\Stdlib\CookieManagerInterface) without a Proxy: the Technical guidelines (9.10) forbid injecting it into constructors. Configure Magento\Framework\Stdlib\CookieManagerInterface\Proxy for this argument in di.xml; do not type-hint the Proxy.',
                27,
            ],
        ]);
    }
}
