<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Xml;

use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\Project\ProjectIndexBuilder;
use Silin\MagentoPerfCheck\Xml\LayoutHandles;

final class LayoutHandlesTest extends TestCase
{
    private function handles(): LayoutHandles
    {
        $root = realpath(__DIR__ . '/../../Fixtures/Rules/X-LAY-01/pages/project');
        self::assertIsString($root);

        return LayoutHandles::fromProject($root, (new ProjectIndexBuilder())->moduleRoots($root));
    }

    public function testPagesAreTheHandlesOfControllerActions(): void
    {
        $handles = $this->handles();
        $never = static fn (string $handle): bool => false;
        $cart = static fn (string $handle): bool => $handle === 'acmeshop_cart_index';

        // A handle that only the personal cart page includes is personal; a page decides for itself,
        // whoever includes it: acmeshop_order_print is the page of Controller/Order/PrintAction.php.
        self::assertTrue($handles->isPersonal('acmeshop_included_only', $never, true, $cart));
        self::assertFalse($handles->isPersonal('acmeshop_order_print', $never, true, $cart));
        // A handle that a controller adds to its page, extending its name, follows that page.
        self::assertTrue($handles->isPersonal('acmeshop_cart_index_type_bundle', $never, true, $cart));
        self::assertTrue($handles->isPersonal('hyva_acmeshop_cart_index_type_bundle', $never, true, $cart));
        // The hyva_ copy of an addition is used where the addition is: default.xml includes acmeshop_cart_index_renderers.
        self::assertFalse($handles->isPersonal('hyva_acmeshop_cart_index_renderers', $never, true, $cart));
        // default.xml includes the sidebar: it is on every page.
        self::assertFalse($handles->isPersonal('acmeshop_cart_sidebar', $never, true, $cart));
        // Transaction.php serves "transaction": only reserved words drop an "Action" suffix, so acmeshop_index_trans
        // is no page and follows default.xml, which includes it.
        $named = static fn (string $name): callable => static fn (string $handle): bool => $handle === $name;
        self::assertFalse($handles->isPersonal('acmeshop_index_trans', $named('acmeshop_index_trans')));
        // A handle added to a page by code may be personal itself, also when its page is not.
        self::assertTrue($handles->isPersonal('acmeshop_order_print_extra', $named('acmeshop_order_print_extra')));
        // Acme_ShopExtra declares acmeshop too, without controllers: the route still has the pages of Acme_Shop.
        self::assertFalse($handles->isPersonal('acmeshop_cart_sidebar', static fn (string $handle): bool => str_starts_with($handle, 'acmeshop_')));
        // hyva_shopadmin is a route of its own, not the hyva_ copy of a shopadmin handle.
        self::assertTrue($handles->isPersonal('hyva_shopadmin_index_index', $named('hyva_shopadmin_index_index')));
        // A handle of that route that is no page follows the pages that include it.
        self::assertTrue($handles->isPersonal('hyva_shopadmin_sidebar', $named('hyva_shopadmin_index_index')));
        // An addition to the cart page that the print page includes too is personal only when both pages are.
        self::assertFalse($handles->isPersonal('acmeshop_cart_index_type_virtual', $never, true, $cart));
        $cartAndPrint = static fn (string $handle): bool => in_array($handle, ['acmeshop_cart_index', 'acmeshop_order_print'], true);
        self::assertTrue($handles->isPersonal('acmeshop_cart_index_type_virtual', $never, true, $cartAndPrint));
        // A route without modules serves nothing, so its handles are no pages: default.xml includes this one.
        self::assertFalse($handles->isPersonal('acmeempty_index_index', $named('acmeempty_index_index')));
        // A final class with an attribute on its line serves a page.
        self::assertTrue($handles->isPersonal('acmeshop_index_attributed', $named('acmeshop_index_attributed')));
        // An abstract controller serves no page, and a route in an XML comment is no route: default.xml includes both.
        self::assertFalse($handles->isPersonal('acmeshop_abstractcontroller_view', $named('acmeshop_abstractcontroller_view')));
        self::assertFalse($handles->isPersonal('acmeshop_commented_cart_index', $named('acmeshop_commented_cart_index')));
    }
}
