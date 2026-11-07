<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use DOMDocument;
use DOMElement;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\XmlFile;
use Silin\MagentoPerfCheck\Xml\XmlRule;
use Silin\MagentoPerfCheck\Xml\XPath;

/**
 * X-AREA-01: an observer in the global etc/events.xml on an event that fires only in the
 * admin area, so every other area loads it for nothing. Plugins are left out: a class under
 * Controller\Adminhtml may be a plain service that other areas use.
 */
final class GlobalAreaRule implements XmlRule
{
    public const ID = 'X-AREA-01';

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_CONFIG
            && $file->area === 'global'
            && basename($file->relativePath) === 'events.xml';
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach (XPath::elements($document, '/config/event/observer') as $observer) {
            $event = $observer->parentNode instanceof DOMElement ? $observer->parentNode->getAttribute('name') : '';
            if ($observer->getAttribute('disabled') !== 'true' && self::isAdminEvent($event)) {
                $findings[] = new Finding(self::ID, Severity::Info, $file->relativePath, $observer->getLineNo(), sprintf(
                    '[%s] Observer "%s" on admin event "%s" is declared in the global area, so every area loads it. Move it to etc/adminhtml/events.xml.',
                    self::ID,
                    $observer->getAttribute('name'),
                    $event,
                ), $observer);
            }
        }

        return $findings;
    }

    /**
     * adminhtml_cache_* events also fire from the cache:flush command, which runs in the global area.
     */
    private static function isAdminEvent(string $event): bool
    {
        return (str_starts_with($event, 'adminhtml_') && !str_starts_with($event, 'adminhtml_cache_'))
            || str_starts_with($event, 'controller_action_predispatch_adminhtml_')
            || str_starts_with($event, 'controller_action_postdispatch_adminhtml_');
    }
}
