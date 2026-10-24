<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Config;

use Silin\MagentoPerfCheck\Cli\UsageException;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use Silin\MagentoPerfCheck\Rule\RuleStatus;
use Silin\MagentoPerfCheck\Rule\Severity;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;

/**
 * Reads magento-perf-check.neon. Anything unknown or malformed stops the run with a
 * message naming the key, so a typo never silently changes what is analyzed.
 */
final class ConfigLoader
{
    private const TOP_KEYS = ['paths', 'includeTests', 'rules', 'layout'];
    private const RULE_KEYS = ['enabled', 'severity'];
    private const LAYOUT_KEYS = ['allowlistHandles', 'replaceAllowlist'];

    public function __construct(private readonly RuleRegistry $registry)
    {
    }

    public function load(?string $file): Configuration
    {
        if ($file === null) {
            return new Configuration();
        }
        try {
            $data = Neon::decodeFile($file);
        } catch (NeonException $e) {
            throw new UsageException(sprintf('%s: %s', $file, $e->getMessage()));
        }
        if ($data === null) {
            return new Configuration();
        }
        if (!is_array($data) || array_keys($data) !== ['magentoPerf'] || !is_array($data['magentoPerf'])) {
            throw new UsageException(sprintf('%s: expected a single top-level "magentoPerf" section.', $file));
        }
        $section = $data['magentoPerf'];
        $this->checkKeys($file, 'magentoPerf', $section, self::TOP_KEYS);

        $layout = $section['layout'] ?? [];
        if (!is_array($layout)) {
            throw new UsageException(sprintf('%s: magentoPerf.layout must be a map.', $file));
        }
        $this->checkKeys($file, 'magentoPerf.layout', $layout, self::LAYOUT_KEYS);

        return new Configuration(
            isset($section['paths']) ? $this->stringList($file, 'magentoPerf.paths', $section['paths']) : null,
            isset($section['includeTests']) ? $this->bool($file, 'magentoPerf.includeTests', $section['includeTests']) : null,
            $this->rules($file, $section['rules'] ?? []),
            isset($layout['allowlistHandles']) ? $this->stringList($file, 'magentoPerf.layout.allowlistHandles', $layout['allowlistHandles']) : [],
            isset($layout['replaceAllowlist']) && $this->bool($file, 'magentoPerf.layout.replaceAllowlist', $layout['replaceAllowlist']),
        );
    }

    /**
     * @param array<mixed> $values
     * @param list<string> $allowed
     */
    private function checkKeys(string $file, string $section, array $values, array $allowed): void
    {
        foreach (array_keys($values) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new UsageException(sprintf(
                    '%s: unknown key "%s" in %s. Allowed: %s.',
                    $file,
                    $key,
                    $section,
                    implode(', ', $allowed),
                ));
            }
        }
    }

    /**
     * @return array<string, RuleSettings>
     */
    private function rules(string $file, mixed $rules): array
    {
        if (!is_array($rules)) {
            throw new UsageException(sprintf('%s: magentoPerf.rules must be a map of rule IDs.', $file));
        }
        $result = [];
        foreach ($rules as $id => $settings) {
            $id = (string) $id;
            if ($this->registry->find($id) === null) {
                throw new UsageException(sprintf('%s: "%s" in magentoPerf.rules is not a known rule.', $file, $id));
            }
            if (!is_array($settings)) {
                throw new UsageException(sprintf('%s: magentoPerf.rules.%s must be a map.', $file, $id));
            }
            $this->checkKeys($file, 'magentoPerf.rules.' . $id, $settings, self::RULE_KEYS);
            $severity = null;
            if (isset($settings['severity'])) {
                $severity = is_string($settings['severity']) ? Severity::tryFrom($settings['severity']) : null;
                if ($severity === null) {
                    throw new UsageException(sprintf(
                        '%s: magentoPerf.rules.%s.severity must be one of: error, warning, info.',
                        $file,
                        $id,
                    ));
                }
            }
            $rule = $this->registry->find($id);
            $enabled = isset($settings['enabled'])
                ? $this->bool($file, 'magentoPerf.rules.' . $id . '.enabled', $settings['enabled'])
                : $rule?->status !== RuleStatus::Experimental;
            $result[$id] = new RuleSettings($enabled, $severity);
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    private function stringList(string $file, string $key, mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new UsageException(sprintf('%s: %s must be a list.', $file, $key));
        }
        $strings = [];
        foreach ($value as $item) {
            if (!is_string($item) || $item === '') {
                throw new UsageException(sprintf('%s: %s must contain non-empty strings.', $file, $key));
            }
            $strings[] = $item;
        }

        return $strings;
    }

    private function bool(string $file, string $key, mixed $value): bool
    {
        if (!is_bool($value)) {
            throw new UsageException(sprintf('%s: %s must be true or false.', $file, $key));
        }

        return $value;
    }
}
