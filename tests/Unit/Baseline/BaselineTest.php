<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Baseline;

use MagentoPerfRules\Baseline\Baseline;
use MagentoPerfRules\Cli\UsageException;
use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Rule\Severity;
use PHPUnit\Framework\TestCase;

final class BaselineTest extends TestCase
{
    private function finding(string $file, int $line, string $message = '[P-LOOP-01] repo get in loop'): Finding
    {
        return new Finding('P-LOOP-01', Severity::Error, $file, $line, $message);
    }

    private function roundTrip(Baseline $baseline): Baseline
    {
        $file = tempnam(sys_get_temp_dir(), 'mpr-baseline');
        self::assertIsString($file);
        $baseline->write($file);
        try {
            return Baseline::load($file);
        } finally {
            unlink($file);
        }
    }

    public function testOnlyNewFindingsRemain(): void
    {
        $baseline = $this->roundTrip(Baseline::fromFindings([$this->finding('a.php', 10)]));

        $result = $baseline->filter([$this->finding('a.php', 10), $this->finding('b.php', 5)]);

        self::assertSame(['b.php'], array_map(static fn (Finding $f): string => $f->file, $result->findings));
        self::assertSame(1, $result->hidden);
        self::assertSame(0, $result->unmatched);
    }

    public function testMovedLineStaysHidden(): void
    {
        $baseline = Baseline::fromFindings([$this->finding('a.php', 10)]);

        self::assertSame([], $baseline->filter([$this->finding('a.php', 42)])->findings);
    }

    public function testSecondOccurrenceIsReported(): void
    {
        $baseline = $this->roundTrip(Baseline::fromFindings([$this->finding('a.php', 10)]));

        $result = $baseline->filter([$this->finding('a.php', 10), $this->finding('a.php', 20)]);

        self::assertCount(1, $result->findings);
        self::assertSame(1, $result->hidden);
    }

    public function testFixedIssueCountsAsUnmatched(): void
    {
        $baseline = Baseline::fromFindings([$this->finding('a.php', 10), $this->finding('b.php', 3)]);

        self::assertSame(1, $baseline->filter([$this->finding('a.php', 10)])->unmatched);
    }

    public function testMissingFile(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessageMatches('/does not exist/');

        Baseline::load(__DIR__ . '/missing.json');
    }

    public function testInvalidFile(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'mpr-baseline');
        self::assertIsString($file);
        file_put_contents($file, '{"version": 1, "entries": [{"ruleId": "P-LOOP-01"}]}');

        $this->expectException(UsageException::class);
        $this->expectExceptionMessageMatches('/malformed entry/');
        try {
            Baseline::load($file);
        } finally {
            unlink($file);
        }
    }

    public function testWrittenFileIsSortedAndReadable(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'mpr-baseline');
        self::assertIsString($file);
        Baseline::fromFindings([$this->finding('b.php', 1), $this->finding('a.php', 1), $this->finding('a.php', 2)])->write($file);
        $data = json_decode((string) file_get_contents($file), true);
        unlink($file);

        self::assertSame(
            [
                ['ruleId' => 'P-LOOP-01', 'file' => 'a.php', 'message' => '[P-LOOP-01] repo get in loop', 'count' => 2],
                ['ruleId' => 'P-LOOP-01', 'file' => 'b.php', 'message' => '[P-LOOP-01] repo get in loop', 'count' => 1],
            ],
            is_array($data) ? $data['entries'] : null,
        );
    }
}
