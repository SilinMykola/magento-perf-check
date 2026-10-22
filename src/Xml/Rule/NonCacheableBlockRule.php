<?php

declare(strict_types=1);

namespace MagentoPerfRules\Xml\Rule;

use DOMDocument;
use DOMElement;
use DOMXPath;
use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Rule\Severity;
use MagentoPerfRules\Xml\XmlFile;
use MagentoPerfRules\Xml\XmlRule;

/**
 * X-LAY-01: a block with cacheable="false" makes every page that uses its handle
 * bypass Full Page Cache.
 */
final class NonCacheableBlockRule implements XmlRule
{
    public const ID = 'X-LAY-01';

    /** Handles shared by many or high-traffic pages. */
    private const ERROR_HANDLE_PREFIXES = ['catalog_', 'cms_'];

    /** Personal pages that are not meant to be served from FPC. */
    private const DEFAULT_ALLOWLIST = [
        'checkout_',
        'customer_account_',
        'sales_',
        'wishlist_',
        'multishipping_',
        'paypal_',
    ];

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_LAYOUT
            && in_array($file->area, ['frontend', 'base'], true)
            && $file->handle !== null
            && !$this->startsWithAny($file->handle, self::DEFAULT_ALLOWLIST);
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $handle = (string) $file->handle;
        $severity = $handle === 'default' || $this->startsWithAny($handle, self::ERROR_HANDLE_PREFIXES)
            ? Severity::Error
            : Severity::Warning;

        $findings = [];
        $blocks = (new DOMXPath($document))->query("//block[@cacheable='false']");
        foreach ($blocks === false ? [] : $blocks as $block) {
            if (!$block instanceof DOMElement) {
                continue;
            }
            $name = $block->getAttribute('name');
            $findings[] = new Finding(
                self::ID,
                $severity,
                $file->relativePath,
                $block->getLineNo(),
                sprintf(
                    '[%s] Block "%s" in handle "%s" has cacheable="false": every page that uses this handle is excluded from Full Page Cache.',
                    self::ID,
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
