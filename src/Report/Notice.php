<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Report;

/**
 * Something the analysis could not handle and skipped: an unparsable file,
 * or a PHPStan error that does not come from one of our rules.
 */
final class Notice
{
    /** Part of the analysis did not run, so the result cannot be trusted. */
    public const SOURCE_ANALYSIS_FAILURE = 'analysis-failure';
    public const SOURCE_UNTYPED_RECEIVER = 'untyped-receiver';
    /** A finding in a module disabled in app/etc/config.php, which does not run. */
    public const SOURCE_DISABLED_MODULE = 'disabled-module';
    /** magento/magento-coding-standard is missing, or its phpcs failed (S-* rules). */
    public const SOURCE_CODING_STANDARD = 'coding-standard';
    /** Upgrade rules did not run: neither the installed Magento version nor --target-version is known. */
    public const SOURCE_UPGRADE_VERSION = 'upgrade-version';

    public function __construct(
        public readonly string $source,
        public readonly string $message,
        public readonly ?string $file = null,
        public readonly ?int $line = null,
    ) {
    }
}
