<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
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
    ];

    /** @var list<string> */
    private readonly array $allowlist;

    /**
     * @param list<string> $extraAllowlist handle prefixes from the configuration file
     * @param bool $replaceAllowlist use only $extraAllowlist instead of adding it to the default
     */
    public function __construct(array $extraAllowlist = [], bool $replaceAllowlist = false)
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
            && !$this->startsWithAny($file->handle, $this->allowlist);
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $handle = (string) $file->handle;
        [$id, $severity] = $handle === 'default' || $this->startsWithAny($handle, self::ERROR_HANDLE_PREFIXES)
            ? [self::SHOP_WIDE_ID, Severity::Error]
            : [self::ID, Severity::Warning];

        $xpath = new DOMXPath($document);
        // A page built on the customer account layout is personal, whatever its handle is called.
        $accountPage = $xpath->query("//update[@handle='customer_account']");
        if ($accountPage !== false && $accountPage->length > 0) {
            return [];
        }

        $findings = [];
        $blocks = $xpath->query("//block[@cacheable='false']");
        foreach ($blocks === false ? [] : $blocks as $block) {
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
