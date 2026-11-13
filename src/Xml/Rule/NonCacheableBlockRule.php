<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\LayoutHandles;
use Silin\MagentoPerfCheck\Xml\XmlFile;
use Silin\MagentoPerfCheck\Xml\XmlRule;

/**
 * X-LAY-03 and X-LAY-01: a block with cacheable="false" makes every page that uses its
 * handle bypass Full Page Cache. X-LAY-03 covers the handles shared by many or high-traffic
 * pages, X-LAY-01 all others.
 */
final class NonCacheableBlockRule implements XmlRule
{
    public const ID = 'X-LAY-01';
    public const SHOP_WIDE_ID = 'X-LAY-03';

    /** Handles shared by many or high-traffic pages. */
    private const ERROR_HANDLE_PREFIXES = ['catalog_', 'cms_'];

    /**
     * Personal pages that are not meant to be served from FPC. The list extends the
     * SPEC default with the handles Magento core itself makes uncacheable
     * (docs/runs/m0-core.md).
     */
    private const DEFAULT_ALLOWLIST = [
        // customer
        'customer_',
        'loginascustomer_',
        'newsletter_manage_',
        'review_customer_',
        'downloadable_customer_',
        // checkout and sales
        'checkout_',
        'hyva_checkout_',
        'onestepcheckout_', // the checkout page of one-step checkout modules
        'multishipping_',
        'sales_',
        'shipping_tracking_',
        // payment
        'paypal_',
        'vault_',
        'paymentservicespaypal_vault_',
        // lists
        'wishlist_',
        'catalog_product_compare_',
        // forms bound to one customer or email link
        'productalert_unsubscribe_',
        'sendfriend_',
        // personal pages of known modules (docs/calibration/round-8.md)
        'connector_', // Dotdigital: passcode-gated endpoints its servers fetch, mostly one customer's email content
        'paynl_order_', // Pay.nl: return pages after payment
        'paynl_checkout_',
        'payone_onepage_', // Payone: checkout steps of the current quote
        'payone_amazon_',
        'buckaroo_mrcash_pay', // Buckaroo: one transaction's payment page
        'mpblog_post_history', // Mageplaza Blog: the author's own pages
        'mpblog_post_preview',
        'mpblog_author_information',
        'clerk_clerk_powerstep_', // Clerk: recommendations for what was just added to the cart
        'sharecart_index_download', // Mageplaza Share Cart: a PDF of the current cart
        'mailchimp_cart_loadquote', // Mailchimp: a cart restored from an email link
        'blog_non_cacheable', // Magefan Blog: post preview
    ];

    /** @var list<string> */
    private readonly array $allowlist;

    /** @var array<string, bool> handle => personal, as LayoutHandles answered for this rule's allowlist */
    private array $personal = [];

    /**
     * @param list<string> $extraAllowlist handle prefixes from the configuration file
     * @param bool $replaceAllowlist use only $extraAllowlist instead of adding it to the default
     * @param ?LayoutHandles $handles how the project's handles include each other; without it each file stands alone
     */
    public function __construct(array $extraAllowlist = [], bool $replaceAllowlist = false, private readonly ?LayoutHandles $handles = null)
    {
        $this->allowlist = $replaceAllowlist ? $extraAllowlist : [...self::DEFAULT_ALLOWLIST, ...$extraAllowlist];
    }

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_LAYOUT
            && in_array($file->area, ['frontend', 'base'], true)
            && $file->handle !== null
            && !$this->startsWithAny($file->handle, $this->allowlist)
            && !$this->startsWithAny(self::withoutHyvaPrefix($file->handle), $this->allowlist);
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $handle = (string) $file->handle;
        $page = self::withoutHyvaPrefix($handle);
        [$id, $severity] = $page === 'default' || $this->startsWithAny($page, self::ERROR_HANDLE_PREFIXES)
            ? [self::SHOP_WIDE_ID, Severity::Error]
            : [self::ID, Severity::Warning];

        $xpath = new DOMXPath($document);
        // A page built on the customer account layout is personal, whatever its handle is called.
        // Core merges only the updates that are direct children of the root (Layout\Merge::_fetchRecursiveUpdates()).
        // Core compares the element name without case.
        $accountPage = $xpath->query("/*/*[translate(local-name(), 'UPDATE', 'update') = 'update'][@handle='customer_account']");
        if ($accountPage !== false && $accountPage->length > 0) {
            return [];
        }
        $blocks = $xpath->query("//block[@cacheable='false']");
        if ($blocks === false || $blocks->length === 0) {
            return [];
        }
        // Another file of the handle may make it an account page. A handle that only personal pages include
        // never reaches a cacheable page, but a shop-wide handle may also be added by a controller itself,
        // as catalog_product_view_type_<type> is, so includes are followed only for X-LAY-01.
        if ($this->handles !== null && ($this->personal[$handle] ??= $this->handles->isPersonal($handle, $this->isAllowlisted(...), $id === self::ID))) {
            return [];
        }

        $findings = [];
        foreach ($blocks as $block) {
            if (!$block instanceof DOMElement) {
                continue;
            }
            $name = $block->getAttribute('name');
            $findings[] = new Finding(
                $id,
                $severity,
                $file->relativePath,
                $block->getLineNo(),
                sprintf(
                    '[%s] Block "%s" in handle "%s" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.',
                    $id,
                    $name !== '' ? $name : $block->getAttribute('class'),
                    $handle,
                ),
                $block,
            );
        }

        return $findings;
    }

    private function isAllowlisted(string $handle): bool
    {
        // LayoutHandles strips a hyva_ prefix itself.
        return $this->startsWithAny($handle, $this->allowlist);
    }

    /**
     * A Hyvä theme adds hyva_<handle> next to every handle of the page, so hyva_default.xml
     * applies to the same pages as default.xml.
     */
    private static function withoutHyvaPrefix(string $handle): string
    {
        return str_starts_with($handle, 'hyva_') ? substr($handle, strlen('hyva_')) : $handle;
    }

    /**
     * @param list<string> $prefixes
     */
    private function startsWithAny(string $handle, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($handle, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
