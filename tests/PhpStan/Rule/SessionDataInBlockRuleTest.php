<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
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
        return new SessionDataInBlockRule($this->createReflectionProvider(), self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/fpc.php'], [
            ['[P-FPC-02] Acme\\Promo\\Block\\PromoBlock reads Magento\\Customer\\Model\\Session::getCustomerId() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.', 21],
            ['[P-FPC-02] Acme\\Promo\\ViewModel\\MiniCart reads Magento\\Checkout\\Model\\Session::getQuote() during rendering. On cacheable pages the session is depersonalized, so this data is missing or cached for everyone; load it through customer data sections.', 44],
        ]);
    }

    public function testClassOnlyOnUncacheablePagesIsLeftOut(): void
    {
        $message = static fn (string $class, string $session, string $method): string => sprintf(
            '[P-FPC-02] %s reads %s::%s() during rendering. On cacheable pages the session is depersonalized, '
            . 'so this data is missing or cached for everyone; load it through customer data sections.',
            $class,
            $session,
            $method,
        );

        // Left out: CartOnly, OnNonCacheablePage, AccountOnly, CompareOnly and CartModel, placed only on uncacheable pages.
        $this->analyse([__DIR__ . '/../Fixtures/fpc-placement.php'], [
            [$message('Acme\\Placed\\Block\\SessionBlock', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 17],
            [$message('Acme\\Placed\\Block\\Mixed', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 53],
            [$message('Acme\\Placed\\Block\\OnEveryPage', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 62],
            [$message('Acme\\Placed\\Block\\CartParent', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 71],
            [$message('Acme\\Placed\\Block\\ByReference', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 82],
            [$message('Acme\\Placed\\Block\\IncludedByDefault', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 91],
            [$message('Acme\\Placed\\Block\\CommentedPage', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 100],
            [$message('Acme\\Placed\\Block\\ConfigPage', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 109],
            [$message('Acme\\Placed\\Block\\RemovedPage', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 118],
            [$message('Acme\\Placed\\Block\\RemovedByOne', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 127],
            [$message('Acme\\Placed\\Block\\ParentRemoved', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 136],
            [$message('Acme\\Placed\\Block\\ParentIfconfig', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 145],
            [$message('Acme\\Placed\\Block\\Unplaced', 'Magento\\Customer\\Model\\Session', 'getCustomerId'), 163],
            [$message('Acme\\Placed\\ViewModel\\ItemModel', 'Magento\\Checkout\\Model\\Session', 'getQuote'), 192],
        ]);
    }
}
