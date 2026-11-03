<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Docs;

use PHPUnit\Framework\TestCase;

/**
 * Relative links in the README and the user documentation point at files that exist
 * and that ship in the Composer archive, so they also work under vendor/.
 */
final class RelativeLinksTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../..';

    public function testRelativeLinksResolve(): void
    {
        foreach ($this->relativeLinks() as [$document, $path]) {
            self::assertFileExists(self::ROOT . '/' . $path, $document . ' links ' . $path);
        }
    }

    public function testRelativeLinksDoNotPointAtExportIgnoredFiles(): void
    {
        $ignored = $this->exportIgnored();
        foreach ($this->relativeLinks() as [$document, $path]) {
            foreach ($ignored as $prefix) {
                self::assertFalse(
                    $path === $prefix || str_starts_with($path, $prefix . '/'),
                    sprintf('%s links %s, which is not in the Composer archive; link it on GitHub instead', $document, $path),
                );
            }
        }
    }

    /**
     * @return list<array{string, string}> document and the linked path relative to the repository root
     */
    private function relativeLinks(): array
    {
        $documents = ['README.md', 'CHANGELOG.md', 'resources/skill/magento-perf-check/SKILL.md'];
        foreach ([...glob(self::ROOT . '/docs/*.md') ?: [], ...glob(self::ROOT . '/docs/rules/*.md') ?: []] as $file) {
            $documents[] = substr($file, strlen(self::ROOT) + 1);
        }
        $documents = array_values(array_filter(
            $documents,
            fn (string $document): bool => !$this->isExportIgnored($document),
        ));

        $links = [];
        foreach ($documents as $document) {
            preg_match_all('/\]\(([^)#\s]+)(?:#[^)]*)?\)/', (string) file_get_contents(self::ROOT . '/' . $document), $matches);
            foreach ($matches[1] as $link) {
                if (preg_match('#^[a-z]+:#', $link) === 1) {
                    continue;
                }
                $links[] = [$document, self::normalize(dirname($document) . '/' . $link)];
            }
        }

        return $links;
    }

    private function isExportIgnored(string $path): bool
    {
        foreach ($this->exportIgnored() as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string> paths marked export-ignore in .gitattributes, without the leading slash
     */
    private function exportIgnored(): array
    {
        preg_match_all('#^/(\S+)\s+export-ignore\s*$#m', (string) file_get_contents(self::ROOT . '/.gitattributes'), $matches);

        return array_map(static fn (string $path): string => rtrim($path, '/'), $matches[1]);
    }

    private static function normalize(string $path): string
    {
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($parts);
            } else {
                $parts[] = $part;
            }
        }

        return implode('/', $parts);
    }
}
