<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Project;

use PHPUnit\Framework\TestCase;

/**
 * PHPStan runs code from this directory, so it has to be private. TMPDIR is read when PHP
 * starts, hence the subprocess.
 */
final class WorkDirectoryTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        if (!function_exists('posix_geteuid')) {
            self::markTestSkipped('needs the posix extension');
        }
        $this->tmp = (string) realpath(sys_get_temp_dir()) . '/mpc-workdir-' . bin2hex(random_bytes(6));
        mkdir($this->tmp);
    }

    protected function tearDown(): void
    {
        $base = $this->tmp . '/magento-perf-check-' . posix_geteuid();
        if (is_link($base)) {
            unlink($base);
        } elseif (is_dir($base)) {
            rmdir($base);
        }
        rmdir($this->tmp . '/elsewhere');
        rmdir($this->tmp);
    }

    /**
     * @return array{int, string}
     */
    private function workDirectory(): array
    {
        $autoload = var_export((string) realpath(__DIR__ . '/../../../vendor/autoload.php'), true);
        $code = 'require ' . $autoload . '; try { echo Silin\MagentoPerfCheck\Project\WorkDirectory::forProject("/x"); }'
            . ' catch (Silin\MagentoPerfCheck\Cli\UsageException $e) { echo $e->getMessage(); exit(3); }';
        exec('TMPDIR=' . escapeshellarg($this->tmp) . ' ' . escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($code), $output, $exitCode);

        return [$exitCode, implode("\n", $output)];
    }

    public function testBaseDirectoryIsPrivateAndPerUser(): void
    {
        mkdir($this->tmp . '/elsewhere');

        [$exitCode, $path] = $this->workDirectory();

        self::assertSame(0, $exitCode, $path);
        self::assertStringStartsWith($this->tmp . '/magento-perf-check-' . posix_geteuid() . '/', $path);
        self::assertSame(0700, fileperms(dirname($path)) & 0777);
    }

    public function testSymlinkInPlaceOfTheBaseDirectoryIsRefused(): void
    {
        mkdir($this->tmp . '/elsewhere');
        symlink($this->tmp . '/elsewhere', $this->tmp . '/magento-perf-check-' . posix_geteuid());

        [$exitCode, $message] = $this->workDirectory();

        self::assertSame(3, $exitCode, $message);
        self::assertStringContainsString('belongs to another user', $message);
    }
}
