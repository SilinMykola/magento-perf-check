<?php

declare(strict_types=1);

namespace MagentoPerfRules\Report;

use MagentoPerfRules\Rule\Severity;

/**
 * GitHub Actions workflow commands: one annotation per finding on the pull request.
 */
final class GithubReporter implements Reporter
{
    public function render(AnalysisResult $result): string
    {
        $out = '';
        foreach ($result->findings as $finding) {
            $level = match ($finding->severity) {
                Severity::Error => 'error',
                Severity::Warning => 'warning',
                Severity::Info => 'notice',
            };
            $properties = ['file=' . self::property($finding->file)];
            if ($finding->line > 0) {
                $properties[] = 'line=' . $finding->line;
            }
            $properties[] = 'title=' . self::property($finding->ruleId);
            $out .= sprintf("::%s %s::%s\n", $level, implode(',', $properties), self::data($finding->message));
        }

        return $out;
    }

    private static function data(string $value): string
    {
        return strtr($value, ['%' => '%25', "\r" => '%0D', "\n" => '%0A']);
    }

    private static function property(string $value): string
    {
        return strtr(self::data($value), [':' => '%3A', ',' => '%2C']);
    }
}
