<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Docs;

use PHPUnit\Framework\TestCase;

/**
 * Relative links in the README and the user documentation point at files that exist.
 */
final class RelativeLinksTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../..';

    public function testRelativeLinksResolve(): void
    {
        $documents = ['README.md', 'CHANGELOG.md', ...array_map(
            static fn (string $path): string => 'docs/' . basename($path),
            glob(self::ROOT . '/docs/*.md') ?: [],
        )];
        foreach ($documents as $document) {
            preg_match_all('/\]\(([^)#\s]+)(?:#[^)]*)?\)/', (string) file_get_contents(self::ROOT . '/' . $document), $links);
            foreach ($links[1] as $link) {
                if (preg_match('#^[a-z]+:#', $link) === 1) {
                    continue;
                }
                self::assertFileExists(self::ROOT . '/' . dirname($document) . '/' . $link, $document . ' links ' . $link);
            }
        }
    }
}
