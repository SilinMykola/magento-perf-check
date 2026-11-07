<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Project;

use Silin\MagentoPerfCheck\Cli\UsageException;

/**
 * Where the generated PHPStan configuration, its result cache and the project index live.
 * PHPStan runs code from here, so on a shared machine the directory must be this user's alone.
 */
final class WorkDirectory
{
    public static function forProject(string $projectRoot): string
    {
        return self::base() . '/' . md5($projectRoot);
    }

    private static function base(): string
    {
        $uid = function_exists('posix_geteuid') ? posix_geteuid() : null;
        $base = sys_get_temp_dir() . '/magento-perf-check' . ($uid !== null ? '-' . $uid : '');
        if (!is_dir($base) && !@mkdir($base, 0700, true) && !is_dir($base)) {
            throw new UsageException('Cannot create temp directory ' . $base);
        }
        if ($uid !== null && (is_link($base) || fileowner($base) !== $uid)) {
            throw new UsageException(sprintf('Temp directory %s belongs to another user. Remove it or set TMPDIR.', $base));
        }

        return $base;
    }
}
