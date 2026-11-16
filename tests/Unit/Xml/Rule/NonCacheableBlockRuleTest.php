<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Xml\Rule;

use Silin\MagentoPerfCheck\Project\ProjectIndexBuilder;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\LayoutHandles;
use Silin\MagentoPerfCheck\Xml\Rule\NonCacheableBlockRule;
use Silin\MagentoPerfCheck\Xml\XmlAnalyzer;
use PHPUnit\Framework\TestCase;

final class NonCacheableBlockRuleTest extends TestCase
{
    private const FIXTURE = __DIR__ . '/../../../Fixtures/Rules/X-LAY-01';

    /**
     * @return list<Finding>
     */
    private function findings(): array
    {
        $root = realpath(self::FIXTURE . '/project');
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new NonCacheableBlockRule()]))
            ->analyze(['app/code', 'app/design']);
        self::assertSame([], $result->notices);

        return $result->findings;
    }

    public function testMatchesGoldenFile(): void
    {
        $actual = array_map(
            static fn (Finding $f): array => ['file' => $f->file, 'line' => $f->line, 'severity' => $f->severity->value],
            $this->findings(),
        );
        usort($actual, static fn (array $a, array $b): int => [$a['file'], $a['line']] <=> [$b['file'], $b['line']]);

        $expected = json_decode((string) file_get_contents(self::FIXTURE . '/expected.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($expected, $actual);
    }

    public function testMessageNamesHandleAndBlock(): void
    {
        $default = array_values(array_filter(
            $this->findings(),
            static fn (Finding $f): bool => str_ends_with($f->file, 'frontend/layout/default.xml'),
        ));

        self::assertCount(1, $default);
        self::assertSame(NonCacheableBlockRule::SHOP_WIDE_ID, $default[0]->ruleId);
        self::assertStringStartsWith('[X-LAY-03]', $default[0]->message);
        self::assertStringContainsString('"promo.banner"', $default[0]->message);
        self::assertStringContainsString('"default"', $default[0]->message);
    }

    public function testHyvaHandleIsClassifiedAsTheHandleItExtends(): void
    {
        $hyvaDefault = array_values(array_filter(
            $this->findings(),
            static fn (Finding $f): bool => str_ends_with($f->file, 'frontend/layout/hyva_default.xml'),
        ));

        self::assertCount(1, $hyvaDefault);
        self::assertSame(NonCacheableBlockRule::SHOP_WIDE_ID, $hyvaDefault[0]->ruleId);
        self::assertStringContainsString('"hyva_default"', $hyvaDefault[0]->message);
    }

    public function testOtherHandlesKeepTheirOwnRule(): void
    {
        $contact = array_values(array_filter(
            $this->findings(),
            static fn (Finding $f): bool => str_ends_with($f->file, '/contact_index_index.xml'),
        ));

        self::assertCount(1, $contact);
        self::assertSame(NonCacheableBlockRule::ID, $contact[0]->ruleId);
        self::assertStringStartsWith('[X-LAY-01]', $contact[0]->message);
    }

    public function testHandlesAreTracedThroughTheirIncludes(): void
    {
        $root = realpath(self::FIXTURE . '/includes/project');
        self::assertIsString($root);
        $handles = LayoutHandles::fromProject($root, (new ProjectIndexBuilder())->moduleRoots($root));
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new NonCacheableBlockRule([], false, $handles)]))
            ->analyze(['app/code', 'app/design']);
        $reported = array_map(static fn (Finding $f): string => $f->ruleId . ' ' . $f->file . ':' . $f->line, $result->findings);
        sort($reported);

        // Not reported: handles that only personal pages include, directly, through a chain, through a cycle,
        // through a hyva_ copy of an order page or besides a disabled module; their hyva_ copy; theme files of
        // account pages, also of a route's page and a shop-wide handle. Reported: handles that a public page includes, also from view/base or a theme, a
        // hyva_ copy that a public page includes or of a public handle, a route's own handle, a shop-wide handle (X-LAY-03 does not
        // follow includes), a nested include or account mark, cycles alone,
        // and an account mark in a theme folder of a disabled or missing module.
        $layout = 'app/code/Acme/Fee/view/frontend/layout/';
        self::assertSame([
            'X-LAY-01 ' . $layout . 'acme_base_only.xml:4',
            'X-LAY-01 ' . $layout . 'acme_cycle_a.xml:5',
            'X-LAY-01 ' . $layout . 'acme_cycle_b.xml:5',
            'X-LAY-01 ' . $layout . 'acme_loop.xml:5',
            'X-LAY-01 ' . $layout . 'acme_missing_account.xml:4',
            'X-LAY-01 ' . $layout . 'acme_nested.xml:4',
            'X-LAY-01 ' . $layout . 'acme_nested_account.xml:5',
            'X-LAY-01 ' . $layout . 'acme_page_index.xml:4',
            'X-LAY-01 ' . $layout . 'acme_theme_account.xml:4',
            'X-LAY-01 ' . $layout . 'acme_theme_only.xml:4',
            'X-LAY-01 ' . $layout . 'acme_vendor_theme_only.xml:4',
            'X-LAY-01 ' . $layout . 'acme_widget.xml:4',
            'X-LAY-01 ' . $layout . 'acme_widget_catalog.xml:4',
            'X-LAY-01 ' . $layout . 'acmeroute_index_index.xml:4',
            'X-LAY-01 ' . $layout . 'hyva_acme_hyva_pub.xml:4',
            'X-LAY-01 ' . $layout . 'hyva_acme_widget.xml:4',
            'X-LAY-01 ' . $layout . 'hyva_acmeroute_index_index.xml:4',
            'X-LAY-01 ' . $layout . 'hyva_acmeroute_shared_index.xml:4',
            'X-LAY-03 ' . $layout . 'catalog_product_view_type_simple.xml:4',
        ], $reported);
    }

    public function testAllowlistNamesPagesAndIncludedHandlesFollowTheirPages(): void
    {
        $root = realpath(self::FIXTURE . '/pages/project');
        self::assertIsString($root);
        $handles = LayoutHandles::fromProject($root, (new ProjectIndexBuilder())->moduleRoots($root));
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new NonCacheableBlockRule(['acmeshop_'], false, $handles)]))
            ->analyze(['app/code']);

        // acmeshop_cart_index and acmeshop_order_print (PrintAction) are allowlisted pages, acmeshop_cart_index_type_bundle
        // extends a page, and acmeshop_code_added is added by code, as no layout includes it. acmeshop_cart_sidebar is
        // no page: default.xml includes it, so it is on every page, allowlisted prefix or not.
        // acmeshop_cart_adminhtml_index has no page either (Router\ActionList drops "adminhtml" anywhere in the path),
        // and acmeshop_cart_index_renderers, which default.xml includes, follows default.xml as well as the cart page.
        // An abstract controller serves no page, and hyva_shopadmin_sidebar follows hyva_shopadmin_index_index, which
        // the allowlist does not name.
        self::assertSame(
            [
                'app/code/Acme/HyvaShop/view/frontend/layout/hyva_shopadmin_sidebar.xml',
                'app/code/Acme/Shop/view/frontend/layout/acmeshop_abstractcontroller_view.xml',
                'app/code/Acme/Shop/view/frontend/layout/acmeshop_cart_adminhtml_index.xml',
                'app/code/Acme/Shop/view/frontend/layout/acmeshop_cart_index_renderers.xml',
                'app/code/Acme/Shop/view/frontend/layout/acmeshop_cart_sidebar.xml',
            ],
            array_map(static fn (Finding $f): string => $f->file, $result->findings),
        );
    }

    public function testWithoutTheIndexEachFileStandsAlone(): void
    {
        $root = realpath(self::FIXTURE . '/includes/project');
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new NonCacheableBlockRule()]))->analyze(['app/code', 'app/design']);

        self::assertCount(32, $result->findings);
    }
}
