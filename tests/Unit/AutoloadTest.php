<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * PHPStan loads this package's autoloader, so a Magento class in it replaces the project's
 * real one when the tool runs from a checkout, and findings differ from an installed package.
 */
final class AutoloadTest extends TestCase
{
    public function testAutoloaderHasNoMagentoClasses(): void
    {
        /** @var array<string, string> $classMap */
        $classMap = require __DIR__ . '/../../vendor/composer/autoload_classmap.php';

        $magento = array_filter(array_keys($classMap), static fn (string $class): bool => str_starts_with($class, 'Magento\\'));

        // A P-FPC-05 fixture needs this name. From a checkout, a project that names this core
        // controller gets the fixture; that is rare, and an installed package has no tests.
        self::assertSame(['Magento\\Customer\\Controller\\Section\\Load'], array_values($magento));
    }
}
