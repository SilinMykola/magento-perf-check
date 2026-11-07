<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Runs the real PHPStan binary, so levels and ignore comments behave as in a user's project.
 */
final class PhpStanCliTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';

    /**
     * @return list<array{message: string, line: int, identifier?: string}>
     */
    private function analyse(string $path, int $level, bool $experimental = false): array
    {
        $config = tempnam(sys_get_temp_dir(), 'mpr-neon');
        self::assertIsString($config);
        rename($config, $config .= '.neon');
        file_put_contents($config, sprintf(
            "includes:\n    - %s/extension.neon\n%sparameters:\n    level: %d\n    tmpDir: %s\n    bootstrapFiles:\n        - %s/tests/bootstrap.php\n",
            realpath(self::ROOT),
            $experimental ? '    - ' . realpath(self::ROOT) . "/extension-experimental.neon\n" : '',
            $level,
            sys_get_temp_dir() . '/mpr-phpstan-test',
            realpath(self::ROOT),
        ));

        $command = [
            PHP_BINARY,
            self::ROOT . '/vendor/bin/phpstan',
            'analyse',
            '--no-progress',
            '--error-format=json',
            '--configuration=' . $config,
            $path,
        ];
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, self::ROOT);
        self::assertIsResource($process);
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        proc_close($process);
        unlink($config);

        /** @var array{files: array<string, array{messages: list<array{message: string, line: int, identifier?: string}>}>} $result */
        $result = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR) ?? self::fail($stderr);
        $messages = [];
        foreach ($result['files'] as $file) {
            array_push($messages, ...$file['messages']);
        }

        return $messages;
    }

    public function testRuleReportsAtLevelZeroAndHonoursIgnoreComment(): void
    {
        $messages = $this->analyse('tests/Integration/Fixtures/phpstan-cli/Suppression.php', 0);

        self::assertCount(1, $messages, json_encode($messages, JSON_THROW_ON_ERROR));
        self::assertSame(21, $messages[0]['line']);
        self::assertStringStartsWith('[P-LOOP-01]', $messages[0]['message']);
    }

    public function testExperimentalRuleIsOffWithTheMainExtensionOnly(): void
    {
        self::assertSame([], $this->analyse('tests/Integration/Fixtures/phpstan-cli/LevelZero.php', 0));
    }

    public function testCollectionRuleReportsAtLevelZero(): void
    {
        // P-COL-02 is experimental since calibration: it runs with extension-experimental.neon.
        $messages = $this->analyse('tests/Integration/Fixtures/phpstan-cli/LevelZero.php', 0, true);

        self::assertCount(1, $messages, json_encode($messages, JSON_THROW_ON_ERROR));
        self::assertSame(13, $messages[0]['line']);
        self::assertStringStartsWith('[P-COL-02]', $messages[0]['message']);
    }
}
