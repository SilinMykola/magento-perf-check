<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Calibration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../bin/calibration/registration-check.php';

final class RegistrationCheckTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function standard(): iterable
    {
        yield 'use and module' => [<<<'PHP'
            <?php
            /**
             * Copyright notice
             */
            use Magento\Framework\Component\ComponentRegistrar;

            ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Acme_Module', __DIR__);
            PHP];
        yield 'fully qualified, double quotes, strict types' => [<<<'PHP'
            <?php
            declare(strict_types=1);
            \Magento\Framework\Component\ComponentRegistrar::register(
                \Magento\Framework\Component\ComponentRegistrar::MODULE,
                "Acme_Module",
                __DIR__
            );
            PHP];
        yield 'theme' => [<<<'PHP'
            <?php
            use Magento\Framework\Component\ComponentRegistrar;
            ComponentRegistrar::register(ComponentRegistrar::THEME, 'frontend/Hyva/default', __DIR__);
            PHP];
    }

    #[DataProvider('standard')]
    public function testStandardRegistrationPasses(string $code): void
    {
        self::assertTrue(isStandardRegistration($code));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function other(): iterable
    {
        yield 'reads a .env file' => [<<<'PHP'
            <?php
            use Magento\Framework\Component\ComponentRegistrar;
            ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Acme_Module', __DIR__);
            (new \Dotenv\Dotenv(__DIR__))->load();
            PHP];
        yield 'guarded by a check' => [<<<'PHP'
            <?php
            use Magento\Framework\Component\ComponentRegistrar;
            if (!class_exists('Acme\Other')) {
                ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Acme_Module', __DIR__);
            }
            PHP];
        yield 'name from a class' => [<<<'PHP'
            <?php
            use Magento\Framework\Component\ComponentRegistrar;
            ComponentRegistrar::register(ComponentRegistrar::MODULE, \Acme\Module\Helper::NAME, __DIR__);
            PHP];
        yield 'other path' => [<<<'PHP'
            <?php
            use Magento\Framework\Component\ComponentRegistrar;
            ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Acme_Module', dirname(__DIR__));
            PHP];
        yield 'two registrations' => [<<<'PHP'
            <?php
            use Magento\Framework\Component\ComponentRegistrar;
            ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Acme_One', __DIR__);
            ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Acme_Two', __DIR__);
            PHP];
        yield 'output after a closing tag' => [<<<'PHP'
            <?php
            use Magento\Framework\Component\ComponentRegistrar;
            ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Acme_Module', __DIR__);
            ?>
            text
            PHP];
        yield 'library type' => [<<<'PHP'
            <?php
            use Magento\Framework\Component\ComponentRegistrar;
            ComponentRegistrar::register(ComponentRegistrar::LIBRARY, 'acme/lib', __DIR__);
            PHP];
    }

    #[DataProvider('other')]
    public function testAnythingElseFails(string $code): void
    {
        self::assertFalse(isStandardRegistration($code));
    }
}
