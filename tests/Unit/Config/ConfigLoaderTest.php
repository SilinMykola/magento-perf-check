<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Config;

use Silin\MagentoPerfCheck\Cli\Options;
use Silin\MagentoPerfCheck\Cli\UsageException;
use Silin\MagentoPerfCheck\Config\ConfigLoader;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\Severity;
use PHPUnit\Framework\TestCase;

final class ConfigLoaderTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/../../Fixtures';

    private function load(string $name): \Silin\MagentoPerfCheck\Config\Configuration
    {
        return (new ConfigLoader(new RuleRegistry()))->load(self::FIXTURES . '/Config/' . $name);
    }

    public function testDefaultFileInProjectRoot(): void
    {
        $project = realpath(self::FIXTURES . '/Project/configured');
        self::assertIsString($project);

        $options = Options::fromArgv(['--project=' . $project], __DIR__);

        self::assertSame($project . '/magento-perf-check.neon', $options->configFile);
        self::assertTrue((new ConfigLoader(new RuleRegistry()))->load($options->configFile)->includeTests);
    }

    public function testExplicitFileWinsOverProjectFile(): void
    {
        $project = realpath(self::FIXTURES . '/Project/configured');
        self::assertIsString($project);

        $options = Options::fromArgv(['--project=' . $project, '--config=../../Fixtures/Config/rules.neon'], __DIR__);

        self::assertSame(__DIR__ . '/../../Fixtures/Config/rules.neon', $options->configFile);
    }

    public function testExplicitFileMissing(): void
    {
        $project = realpath(self::FIXTURES . '/Project/configured');
        self::assertIsString($project);

        $this->expectException(UsageException::class);
        $this->expectExceptionMessageMatches('/Configuration file .*missing\.neon does not exist/');

        Options::fromArgv(['--project=' . $project, '--config=missing.neon'], __DIR__);
    }

    public function testRuleSettingsRead(): void
    {
        $settings = $this->load('rules.neon')->rules['P-LOOP-01'];

        self::assertTrue($settings->enabled);
        self::assertSame(Severity::Info, $settings->severity);
    }

    public function testUnknownKey(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage('unknown key "path" in magentoPerf');

        $this->load('unknown-key.neon');
    }

    public function testUnknownRuleId(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage('"X-LAY-99" in magentoPerf.rules is not a known rule');

        $this->load('unknown-rule.neon');
    }

    public function testInvalidSeverity(): void
    {
        $this->expectException(UsageException::class);
        $this->expectExceptionMessage('magentoPerf.rules.X-LAY-01.severity must be one of: error, warning, info');

        $this->load('bad-severity.neon');
    }
}
