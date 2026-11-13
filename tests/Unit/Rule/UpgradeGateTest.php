<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Rule;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\Rule\MagentoVersion;
use Silin\MagentoPerfCheck\Rule\RuleDefinition;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\RuleStatus;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Rule\Source;
use Silin\MagentoPerfCheck\Rule\SourceLevel;
use Silin\MagentoPerfCheck\Rule\UpgradeGate;

final class UpgradeGateTest extends TestCase
{
    public function testParse(): void
    {
        self::assertSame('2.4.9', (string) MagentoVersion::parse('2.4.9'));
        self::assertSame('2.4.8-p3', (string) MagentoVersion::parse('2.4.8-p3'));
        // A pre-release counts as its release.
        self::assertSame('2.4.9', (string) MagentoVersion::parse('2.4.9-rc2'));
        self::assertSame('2.4.9', (string) MagentoVersion::parse('2.4.9-beta1'));
        self::assertNull(MagentoVersion::parse('latest'));
        self::assertNull(MagentoVersion::parse('2.4'));
        self::assertNull(MagentoVersion::parse('2.4.9-p'));
    }

    /**
     * @return iterable<string, array{string, list<string>, bool}>
     */
    public static function versions(): iterable
    {
        yield 'same release' => ['2.4.9', ['2.4.9'], true];
        yield 'patch of that release' => ['2.4.9-p1', ['2.4.9'], true];
        yield 'older line' => ['2.4.8', ['2.4.9'], false];
        yield 'newer line' => ['2.4.10', ['2.4.9'], true];
        yield 'patch below the one listed for its line' => ['2.4.8-p2', ['2.4.9', '2.4.8-p3'], false];
        yield 'patch listed for its line' => ['2.4.8-p3', ['2.4.9', '2.4.8-p3'], true];
        yield 'line between two listed lines' => ['2.4.8-p5', ['2.4.9', '2.4.7-p8'], false];
        yield 'line older than every one listed' => ['2.4.6', ['2.4.9', '2.4.7-p8'], false];
        yield 'no version listed' => ['2.4.9', [], false];
    }

    /**
     * @param list<string> $firstVersions
     */
    #[DataProvider('versions')]
    public function testHas(string $version, array $firstVersions, bool $expected): void
    {
        $parsed = MagentoVersion::parse($version);
        self::assertNotNull($parsed);
        self::assertSame($expected, $parsed->has($firstVersions));
    }

    public function testRunsWhenTheInstalledOrTheTargetVersionHasTheChange(): void
    {
        $rule = self::rule(['2.4.8-p3', '2.4.7-p8']);
        $v = static fn (string $version): MagentoVersion => MagentoVersion::parse($version) ?? throw new \LogicException($version);

        // The installed patch already has the change; the target does not.
        self::assertTrue((new UpgradeGate($v('2.4.7-p8'), $v('2.4.8')))->runs($rule));
        self::assertTrue((new UpgradeGate($v('2.4.7'), $v('2.4.8-p3')))->runs($rule));
        self::assertFalse((new UpgradeGate($v('2.4.7'), $v('2.4.8')))->runs($rule));
        self::assertFalse((new UpgradeGate(null, null))->runs($rule));
        // Other rules always run.
        self::assertTrue((new UpgradeGate(null, null))->runs(self::rule([])));
        self::assertTrue((new UpgradeGate(null, null))->runs(null));
    }

    public function testConsoleExecuteResultRuleRunsFromTheSymfony54Patches(): void
    {
        $rule = (new RuleRegistry())->find('U-CLI-02');
        self::assertNotNull($rule);
        $runs = static fn (string $version): bool => (MagentoVersion::parse($version)?->has($rule->changedIn)) === true;

        // Symfony Console 5.4 came with 2.4.4-p12, 2.4.5-p11 and 2.4.6.
        self::assertSame(
            [false, true, false, true, true, true, false],
            array_map($runs, ['2.4.4-p11', '2.4.4-p12', '2.4.5-p10', '2.4.5-p11', '2.4.6', '2.4.9', '2.4.3-p3']),
        );
    }

    public function testMonologAddMethodRuleRunsFrom244(): void
    {
        $rule = (new RuleRegistry())->find('U-LOG-01');
        self::assertNotNull($rule);
        $runs = static fn (string $version): bool => (MagentoVersion::parse($version)?->has($rule->changedIn)) === true;

        // Monolog 2 came with 2.4.4; every 2.4.3 patch release keeps Monolog 1.
        self::assertSame(
            [false, false, true, true, true],
            array_map($runs, ['2.4.3', '2.4.3-p3', '2.4.4', '2.4.4-p2', '2.4.9']),
        );
    }

    public function testInstalledVersionFromTheProject(): void
    {
        $project = sys_get_temp_dir() . '/mpr-version-' . bin2hex(random_bytes(4));
        mkdir($project . '/vendor/magento/magento2-base', 0777, true);
        try {
            self::assertNull(MagentoVersion::installedIn($project));
            file_put_contents($project . '/vendor/magento/magento2-base/composer.json', '{"name": "magento/magento2-base", "version": "2.4.8-p2"}');
            self::assertSame('2.4.8-p2', (string) MagentoVersion::installedIn($project));
        } finally {
            exec('rm -rf ' . escapeshellarg($project));
        }
    }

    public function testEveryUpgradeRuleNamesValidVersions(): void
    {
        $upgradeRules = 0;
        foreach ((new RuleRegistry())->all() as $rule) {
            foreach ($rule->changedIn as $version) {
                self::assertNotNull(MagentoVersion::parse($version), $rule->id . ': ' . $version);
            }
            $upgradeRules += $rule->isUpgradeRule() ? 1 : 0;
            self::assertSame($rule->isUpgradeRule(), str_starts_with($rule->id, 'U-'), $rule->id);
        }
        self::assertGreaterThan(0, $upgradeRules);
    }

    /**
     * @param list<string> $changedIn
     */
    private static function rule(array $changedIn): RuleDefinition
    {
        return new RuleDefinition('U-TEST-01', 'Test', Severity::Error, RuleStatus::Experimental, [new Source(SourceLevel::A, 'https://example.com', 'x')], [], $changedIn);
    }
}
