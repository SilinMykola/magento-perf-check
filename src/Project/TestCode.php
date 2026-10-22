<?php

declare(strict_types=1);

namespace MagentoPerfRules\Project;

/**
 * Which paths count as test code: any directory named Test (module tests, fixtures,
 * Test/_files) and any dev/tests directory (magento2-base test suites).
 */
final class TestCode
{
    public static function contains(string $relativePath): bool
    {
        $segments = explode('/', trim($relativePath, '/'));
        foreach ($segments as $i => $segment) {
            if ($segment === 'Test') {
                return true;
            }
            if ($segment === 'dev' && ($segments[$i + 1] ?? null) === 'tests') {
                return true;
            }
        }

        return false;
    }

    /**
     * PHPStan excludePaths patterns for the test code below an analyzed directory.
     *
     * @return list<string>
     */
    public static function excludePatterns(string $absoluteDir): array
    {
        $dir = rtrim($absoluteDir, '/');

        return [
            $dir . '/Test/*',
            $dir . '/*/Test/*',
            $dir . '/dev/tests/*',
            $dir . '/*/dev/tests/*',
        ];
    }
}
