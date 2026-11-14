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
        self::assertFalse($options->verbose);
        self::assertSame('auto', $options->color);
    }

    public function testVerboseAndColor(): void
    {
        self::assertTrue(Options::fromArgv(['--project=' . $this->project(), '-v'], __DIR__)->verbose);
        $options = Options::fromArgv(['--project=' . $this->project(), '--verbose', '--color=never'], __DIR__);
        self::assertTrue($options->verbose);
        self::assertSame('never', $options->color);
    }

    public function testInvalidColor(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage('--color must be one of: auto, always, never.');

        Options::fromArgv(['--project=' . $this->project(), '--color=yes'], __DIR__);
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

    public function testTargetVersion(): void
    {
        self::assertSame('2.4.9', (string) Options::fromArgv(['--project=' . $this->project(), '--target-version=2.4.9'], __DIR__)->targetVersion);
        self::assertNull(Options::fromArgv(['--project=' . $this->project()], __DIR__)->targetVersion);
    }

    public function testInvalidTargetVersion(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage('--target-version must be a Magento version such as 2.4.9 or 2.4.8-p3, not "latest".');

        Options::fromArgv(['--project=' . $this->project(), '--target-version=latest'], __DIR__);
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

    public function testOutputCannotOverwriteTheBaseline(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage('--output must not be the --baseline file.');

        Options::fromArgv(['--project=' . $this->project(), '--output=b.json', '--baseline=' . __DIR__ . '/b.json'], __DIR__);
    }

    public function testPathsCannotLeaveTheProject(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage("inside the project");

        Options::fromArgv(["--project=" . $this->project(), "--paths=app/../../outside"], "/tmp");
    }

    public function testPathsAreNormalizedAndDeduplicated(): void
    {
        $options = Options::fromArgv(["--project=" . $this->project(), "--paths=app/etc,./app/etc,app//etc/"], "/tmp");

        self::assertSame(["app/etc"], $options->paths);
    }

    public function testOutputCannotOverwriteTheBaselineThroughARelativePath(): void
    {
        $this->expectException(UsageException::class);

        Options::fromArgv(["--project=" . $this->project(), "--baseline=b.json", "--output=./b.json"], sys_get_temp_dir());
    }
}
