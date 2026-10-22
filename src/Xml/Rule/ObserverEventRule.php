<?php

declare(strict_types=1);

namespace MagentoPerfRules\Xml\Rule;

use DOMDocument;
use DOMElement;
use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Rule\Severity;
use MagentoPerfRules\Xml\XmlFile;
use MagentoPerfRules\Xml\XmlRule;
use MagentoPerfRules\Xml\XPath;

/**
 * X-EVT-01, X-EVT-02, X-EVT-03: observers on events that fire far more often than one
 * entity's lifecycle. One class per rule ID, created through the named constructors.
 */
final class ObserverEventRule implements XmlRule
{
    private const GENERIC_MODEL_EVENTS = [
        'model_save_before',
        'model_save_after',
        'model_save_commit_after',
        'model_delete_before',
        'model_delete_after',
        'model_delete_commit_after',
    ];

    private const RENDER_EVENTS = [
        'core_layout_render_element',
        'view_block_abstract_to_html_before',
        'view_block_abstract_to_html_after',
    ];

    /**
     * @param callable(string): ?string $explain message for an event, or null when the event is not reported
     * @param list<string> $exemptModules modules whose observers are not reported
     */
    private function __construct(
        private readonly string $id,
        private $explain,
        private readonly array $exemptModules = [],
    ) {
    }

    public static function loadAfter(): self
    {
        return new self('X-EVT-01', static fn (string $event): ?string => match (true) {
            $event === 'model_load_after' => 'runs after loading any model, of any entity type',
            str_ends_with($event, '_load_after') => 'runs on every load of this entity type',
            default => null,
        });
    }

    public static function genericModelEvents(): self
    {
        return new self('X-EVT-02', static fn (string $event): ?string => in_array($event, self::GENERIC_MODEL_EVENTS, true)
            ? 'runs for every model saved or deleted, not just one entity type'
            : null);
    }

    public static function renderEvents(): self
    {
        return new self(
            'X-EVT-03',
            static fn (string $event): ?string => in_array($event, self::RENDER_EVENTS, true)
                ? 'runs for every block or layout element rendered, hundreds of times per page'
                : null,
            ['Magento_PageCache'],
        );
    }

    public function ruleId(): string
    {
        return $this->id;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_CONFIG
            && basename($file->relativePath) === 'events.xml'
            && !in_array($file->module, $this->exemptModules, true);
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach (XPath::elements($document, '/config/event/observer') as $observer) {
            $event = $observer->parentNode instanceof DOMElement ? $observer->parentNode->getAttribute('name') : '';
            $explanation = ($this->explain)($event);
            if ($explanation === null || $observer->getAttribute('disabled') === 'true') {
                continue;
            }
            $findings[] = new Finding(
                $this->id,
                Severity::Warning,
                $file->relativePath,
                $observer->getLineNo(),
                sprintf(
                    '[%s] Observer "%s" on "%s" %s. Observe a more specific event if one exists.',
                    $this->id,
                    $observer->getAttribute('name'),
                    $event,
                    $explanation,
                ),
                $observer,
            );
        }

        return $findings;
    }
}
