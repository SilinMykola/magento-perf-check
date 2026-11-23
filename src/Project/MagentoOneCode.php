<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Project;

/**
 * Magento 1 code left over in a Magento 2 project, which Magento 2 never runs: the Magento 1 code
 * pools app/code/core, app/code/community and app/code/local, and a folder app/design/<area>/<package>/<theme>
 * that is not a Magento 2 theme (it has neither theme.xml nor registration.php).
 */
final class MagentoOneCode
{
    private const CODE_POOLS = ['core', 'community', 'local'];

    /**
     * @return list<string> absolute directories, sorted
     */
    public static function dirs(string $projectRoot): array
    {
        $dirs = [];
        // Names as listed, not is_dir(): on a case-insensitive file system app/code/local would also match a vendor "Local".
        foreach (array_intersect(@scandir($projectRoot . '/app/code') ?: [], self::CODE_POOLS) as $pool) {
            if (is_dir($projectRoot . '/app/code/' . $pool)) {
                $dirs[] = $projectRoot . '/app/code/' . $pool;
            }
        }
        foreach (glob($projectRoot . '/app/design/*/*/*', GLOB_ONLYDIR) ?: [] as $theme) {
            if (!is_file($theme . '/theme.xml') && !is_file($theme . '/registration.php')) {
                $dirs[] = $theme;
            }
        }
        sort($dirs);

        return $dirs;
    }
}
