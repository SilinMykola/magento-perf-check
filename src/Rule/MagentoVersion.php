<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Rule;

/**
 * A Magento version as upgrade rules compare it: a release line (x.y.z) and a patch number
 * (-pN, 0 without one). A pre-release suffix such as -beta1 or -rc2 is dropped, so a 2.4.9
 * release candidate counts as 2.4.9 (change add-upgrade-check-mode, design D2).
 */
final class MagentoVersion
{
    private function __construct(
        public readonly string $line,
        public readonly int $patch,
    ) {
    }

    public static function parse(string $version): ?self
    {
        if (preg_match('/^v?(\d+\.\d+\.\d+)(?:-p(\d+))?(?:-(?:alpha|beta|rc)\d*)?$/i', trim($version), $m) !== 1) {
            return null;
        }

        return new self($m[1], (int) ($m[2] ?? 0));
    }

    /** The version of Magento installed in a project, from vendor/magento/magento2-base. */
    public static function installedIn(string $projectRoot): ?self
    {
        $composer = @file_get_contents($projectRoot . '/vendor/magento/magento2-base/composer.json');
        $data = $composer !== false ? json_decode($composer, true) : null;

        return is_array($data) && is_string($data['version'] ?? null) ? self::parse($data['version']) : null;
    }

    /**
     * Whether this version has a change that shipped first in the given versions, one per release
     * line: it is at or above the version listed for its own line, or its line is newer than every
     * line listed.
     *
     * @param list<string> $firstVersions
     */
    public function has(array $firstVersions): bool
    {
        $newerThanAll = $firstVersions !== [];
        foreach ($firstVersions as $first) {
            $listed = self::parse($first);
            if ($listed === null) {
                continue;
            }
            if ($listed->line === $this->line) {
                return $this->patch >= $listed->patch;
            }
            if (version_compare($this->line, $listed->line, '<')) {
                $newerThanAll = false;
            }
        }

        return $newerThanAll;
    }

    public function __toString(): string
    {
        return $this->line . ($this->patch > 0 ? '-p' . $this->patch : '');
    }
}
