<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Project;

use Silin\MagentoPerfCheck\Project\TestCode;
use PHPUnit\Framework\TestCase;

final class TestCodeTest extends TestCase
{
    public function testRecognisesTestDirectories(): void
    {
        self::assertTrue(TestCode::contains('app/code/Acme/Promo/Test/Unit/FooTest.php'));
        self::assertTrue(TestCode::contains('vendor/magento/magento2-base/dev/tests/integration/testsuite/X.php'));
        self::assertTrue(TestCode::contains('vendor/magento/module-sales/Test/Fixture/PlaceOrder.php'));
    }

    public function testLeavesOtherCodeAlone(): void
    {
        self::assertFalse(TestCode::contains('app/code/Acme/Promo/Model/Tester.php'));
        self::assertFalse(TestCode::contains('app/code/Acme/TestModule/Model/Foo.php'));
        self::assertFalse(TestCode::contains('app/code/Acme/Promo/dev/README.md'));
    }
}
