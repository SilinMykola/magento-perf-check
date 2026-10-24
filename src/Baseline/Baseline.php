<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Baseline;

use JsonException;
use Silin\MagentoPerfCheck\Cli\UsageException;
use Silin\MagentoPerfCheck\Report\Finding;

/**
 * Known findings of a legacy project. A finding is identified by rule ID, file and
 * message; line numbers are left out so that moved code stays hidden.
 */
final class Baseline
{
    private const VERSION = 1;

    /**
     * @param array<string, int> $counts identity key => number of occurrences
     */
    private function __construct(private readonly array $counts)
    {
    }

    /**
     * @param list<Finding> $findings
     */
    public static function fromFindings(array $findings): self
    {
        $counts = [];
        foreach ($findings as $finding) {
            $key = self::key($finding->ruleId, $finding->file, $finding->message);
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return new self($counts);
    }

    public static function load(string $file): self
    {
        $content = @file_get_contents($file);
        if ($content === false) {
            throw new UsageException(sprintf('Baseline file %s does not exist or is not readable.', $file));
        }
        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UsageException(sprintf('Baseline file %s is not valid JSON: %s', $file, $e->getMessage()));
        }
        if (!is_array($data) || ($data['version'] ?? null) !== self::VERSION || !is_array($data['entries'] ?? null)) {
            throw new UsageException(sprintf('Baseline file %s is not a version %d baseline.', $file, self::VERSION));
        }
        $counts = [];
        foreach ($data['entries'] as $entry) {
            if (!is_array($entry)
                || !is_string($entry['ruleId'] ?? null)
                || !is_string($entry['file'] ?? null)
                || !is_string($entry['message'] ?? null)
                || !is_int($entry['count'] ?? null)
                || $entry['count'] < 1
            ) {
                throw new UsageException(sprintf('Baseline file %s has a malformed entry.', $file));
            }
            $key = self::key($entry['ruleId'], $entry['file'], $entry['message']);
            $counts[$key] = ($counts[$key] ?? 0) + $entry['count'];
        }

        return new self($counts);
    }

    public function write(string $file): void
    {
        $entries = [];
        foreach ($this->counts as $key => $count) {
            /** @var array{string, string, string} $identity */
            $identity = json_decode($key, true, 512, JSON_THROW_ON_ERROR);
            $entries[] = ['ruleId' => $identity[0], 'file' => $identity[1], 'message' => $identity[2], 'count' => $count];
        }
        usort($entries, static fn (array $a, array $b): int => [$a['file'], $a['ruleId'], $a['message']] <=> [$b['file'], $b['ruleId'], $b['message']]);

        $json = json_encode(['version' => self::VERSION, 'entries' => $entries], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (@file_put_contents($file, $json . "\n") === false) {
            throw new UsageException(sprintf('Cannot write baseline file %s.', $file));
        }
    }

    public function size(): int
    {
        return array_sum($this->counts);
    }

    /**
     * Removes findings covered by the baseline, each entry up to its count.
     *
     * @param list<Finding> $findings
     */
    public function filter(array $findings): BaselineResult
    {
        $remaining = $this->counts;
        $kept = [];
        $hidden = 0;
        foreach ($findings as $finding) {
            $key = self::key($finding->ruleId, $finding->file, $finding->message);
            if (($remaining[$key] ?? 0) > 0) {
                --$remaining[$key];
                ++$hidden;
            } else {
                $kept[] = $finding;
            }
        }

        return new BaselineResult($kept, $hidden, array_sum($remaining));
    }

    private static function key(string $ruleId, string $file, string $message): string
    {
        return json_encode([$ruleId, $file, $message], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
