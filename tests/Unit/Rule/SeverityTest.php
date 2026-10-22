<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Rule;

use MagentoPerfRules\Rule\Severity;
use PHPUnit\Framework\TestCase;

final class SeverityTest extends TestCase
{
    public function testErrorMeetsEveryThreshold(): void
    {
        self::assertTrue(Severity::Error->isAtLeast(Severity::Error));
        self::assertTrue(Severity::Error->isAtLeast(Severity::Warning));
        self::assertTrue(Severity::Error->isAtLeast(Severity::Info));
    }

    public function testWarningDoesNotMeetErrorThreshold(): void
    {
        self::assertFalse(Severity::Warning->isAtLeast(Severity::Error));
        self::assertTrue(Severity::Warning->isAtLeast(Severity::Warning));
    }

    public function testInfoMeetsOnlyInfoThreshold(): void
    {
        self::assertFalse(Severity::Info->isAtLeast(Severity::Warning));
        self::assertTrue(Severity::Info->isAtLeast(Severity::Info));
    }
}
