<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Process;

use Silin\MagentoPerfCheck\Cli\UsageException;

/**
 * Runs a command without a shell and returns its exit code, stdout and stderr.
 */
final class Subprocess
{
    /**
     * @param list<string> $command
     * @param string $name what runs, for the error message
     * @return array{int, string, string}
     */
    public static function run(array $command, string $cwd, string $name): array
    {
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd);
        if (!is_resource($process)) {
            throw new UsageException('Cannot start ' . $name . '.');
        }
        // An empty stdin: phpcs, for one, checks what it reads there as a file named STDIN.
        fclose($pipes[0]);
        // Read both pipes without blocking each other: the process can write a lot to either.
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $stdout = '';
        $stderr = '';
        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $read = array_values(array_filter([$pipes[1], $pipes[2]], static fn ($pipe): bool => !feof($pipe)));
            $write = null;
            $except = null;
            if (stream_select($read, $write, $except, 1) === false) {
                break;
            }
            foreach ($read as $pipe) {
                $chunk = (string) fread($pipe, 65536);
                if ($pipe === $pipes[1]) {
                    $stdout .= $chunk;
                } else {
                    $stderr .= $chunk;
                }
            }
        }
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $stdout, $stderr];
    }
}
