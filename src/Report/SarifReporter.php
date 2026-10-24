<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Report;

use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\Severity;

/**
 * SARIF 2.1.0, as read by GitHub code scanning and most CI viewers.
 */
final class SarifReporter implements Reporter
{
    private const SCHEMA = 'https://docs.oasis-open.org/sarif/sarif/v2.1.0/errata01/os/schemas/sarif-schema-2.1.0.json';

    public function __construct(private readonly RuleRegistry $registry)
    {
    }

    public function render(AnalysisResult $result): string
    {
        $ruleIndex = [];
        $rules = [];
        foreach ($result->findings as $finding) {
            if (isset($ruleIndex[$finding->ruleId])) {
                continue;
            }
            $definition = $this->registry->find($finding->ruleId);
            $ruleIndex[$finding->ruleId] = count($rules);
            $rules[] = [
                'id' => $finding->ruleId,
                'shortDescription' => ['text' => $definition !== null ? $definition->title : $finding->ruleId],
                'helpUri' => self::fileUri($this->registry->docUrl($finding->ruleId)),
                'defaultConfiguration' => ['level' => self::level($definition !== null ? $definition->severity : $finding->severity)],
            ];
        }

        $results = [];
        foreach ($result->findings as $finding) {
            $location = ['artifactLocation' => ['uri' => self::relativeUri($finding->file), 'uriBaseId' => '%SRCROOT%']];
            if ($finding->line > 0) {
                $location['region'] = ['startLine' => $finding->line];
            }
            $results[] = [
                'ruleId' => $finding->ruleId,
                'ruleIndex' => $ruleIndex[$finding->ruleId],
                'level' => self::level($finding->severity),
                'message' => ['text' => $finding->message],
                'locations' => [['physicalLocation' => $location]],
            ];
        }

        $log = [
            '$schema' => self::SCHEMA,
            'version' => '2.1.0',
            'runs' => [[
                'tool' => ['driver' => ['name' => 'magento-perf-check', 'rules' => $rules]],
                'originalUriBaseIds' => ['%SRCROOT%' => ['uri' => self::fileUri($result->projectRoot) . '/']],
                'results' => $results,
            ]],
        ];

        return json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
    }

    private static function level(Severity $severity): string
    {
        return match ($severity) {
            Severity::Error => 'error',
            Severity::Warning => 'warning',
            Severity::Info => 'note',
        };
    }

    private static function relativeUri(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    private static function fileUri(string $absolutePath): string
    {
        return 'file://' . self::relativeUri($absolutePath);
    }
}
