<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck;

/**
 * The one place that holds the package version. Bump it with each release and keep
 * CHANGELOG.md in step; between releases it carries a -dev suffix (change m3-release, design D1).
 */
final class Version
{
    public const VERSION = '0.8.0-dev';

    public const REPOSITORY = 'https://github.com/SilinMykola/magento-perf-check';

    public static function isDevelopment(string $version = self::VERSION): bool
    {
        return str_ends_with($version, '-dev');
    }
}
