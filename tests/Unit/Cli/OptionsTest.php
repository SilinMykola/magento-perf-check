<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Cli;

use Silin\MagentoPerfCheck\Cli\Options;
use Silin\MagentoPerfCheck\Cli\UsageException;
use Silin\MagentoPerfCheck\Rule\Severity;
use PHPUnit\Framework\TestCase;

final class OptionsTest extends TestCase
{
    private const PROJECT = __DIR__ . '/../../Fixtures/Project/magento';

    private function project(): string
    {
        $project = realpath(self::PROJECT);
        self::assertIsString($project);

        return $project;
    }

    public function testDefaults(): void
    {
        $options = Options::fromArgv([], $this->project());

        self::assertSame($this->project(), $options->projectRoot);
        // The fixture has no app/design: missing default paths are skipped.
        self::assertSame(['app/code'], $options->paths);
        self::assertSame('table', $options->format);
        self::assertSame(Severity::Error, $options->failOn);
    }

    public function testNotAMagentoProject(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessageMatches('/is not a Magento project: missing app\/etc and vendor\/autoload\.php/');

        Options::fromArgv(['--project=' . __DIR__], __DIR__);
    }

    public function testMissingPath(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessageMatches('/Path "app\/nowhere" \(--paths\) does not exist/');

        Options::fromArgv(['--project=' . $this->project(), '--paths=app/code,app/nowhere'], __DIR__);
    }

    public function testUnknownFormatListsSupportedOnes(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage('Unknown format "xml". Supported formats: table, json, sarif, checkstyle, github.');

        Options::fromArgv(['--project=' . $this->project(), '--format=xml'], __DIR__);
    }

    public function testInvalidFailOn(): void
    {
        $this->expectException(UsageException::class);

        Options::fromArgv(['--project=' . $this->project(), '--fail-on=fatal'], __DIR__);
    }

    public function testUnknownArgument(): void
    {
        $this->expectException(UsageException::class);

        Options::fromArgv(['--projekt=/tmp'], __DIR__);
    }

    public function testExplicitPathsUnderVendorAreKept(): void
    {
        $options = Options::fromArgv(['--project=' . $this->project(), '--paths=vendor,app/code/'], __DIR__);

        self::assertSame(['vendor', 'app/code'], $options->paths);
    }

    public function testBaselineFileIsRelativeToWorkingDirectory(): void
    {
        $options = Options::fromArgv(['--project=' . $this->project(), '--generate-baseline=perf-baseline.json'], '/work/dir');

        self::assertSame('/work/dir/perf-baseline.json', $options->generateBaselineFile);
    }

    public function testVersionDoesNotCheckTheProject(): void
    {
        $options = Options::fromArgv(['--version'], __DIR__);

        self::assertTrue($options->version);
    }

    public function testOutputFileIsRelativeToWorkingDirectory(): void
    {
        $options = Options::fromArgv(['--project=' . $this->project(), '--output=reports/perf.txt'], '/work/dir');

        self::assertSame('/work/dir/reports/perf.txt', $options->outputFile);
    }

    public function testOutputCannotBeCombinedWithBaselineGeneration(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage('--output and --generate-baseline cannot be used together.');

        Options::fromArgv(['--project=' . $this->project(), '--output=a.txt', '--generate-baseline=b.json'], __DIR__);
    }
}
