<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Release;

use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\Version;

final class ChangelogTest extends TestCase
{
    private const CHANGELOG = __DIR__ . '/../../../CHANGELOG.md';

    public function testVersionMatchesChangelog(): void
    {
        self::assertNull(self::mismatch(Version::VERSION, self::newestRelease()));
    }

    public function testReleaseMustMatchNewestEntry(): void
    {
        self::assertNull(self::mismatch('0.1.0', '0.1.0'));
        self::assertNotNull(self::mismatch('0.1.1', '0.1.0'));
    }

    public function testDevelopmentVersionMustBeAheadOfNewestEntry(): void
    {
        self::assertNull(self::mismatch('0.2.0-dev', '0.1.0'));
        self::assertNotNull(self::mismatch('0.1.0-dev', '0.1.0'));
    }

    /**
     * Why the code version and the newest released changelog entry disagree, or null when they agree.
     */
    private static function mismatch(string $version, string $newestRelease): ?string
    {
        if (Version::isDevelopment($version)) {
            $base = substr($version, 0, -strlen('-dev'));

            return version_compare($base, $newestRelease, '>')
                ? null
                : sprintf('Development version %s must be ahead of the newest release %s in CHANGELOG.md.', $version, $newestRelease);
        }

        return $version === $newestRelease
            ? null
            : sprintf('Version %s has no matching newest entry in CHANGELOG.md (found %s).', $version, $newestRelease);
    }

    private static function newestRelease(): string
    {
        $changelog = file_get_contents(self::CHANGELOG);
        self::assertIsString($changelog);
        self::assertSame(1, preg_match('/^## \[(\d+\.\d+\.\d+)\] - \d{4}-\d{2}-\d{2}$/m', $changelog, $m), 'CHANGELOG.md has no released entry');

        return $m[1];
    }
}
