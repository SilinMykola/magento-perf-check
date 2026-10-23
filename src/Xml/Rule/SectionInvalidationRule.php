<?php

declare(strict_types=1);

namespace MagentoPerfRules\Xml\Rule;

use DOMDocument;
use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Rule\Severity;
use MagentoPerfRules\Xml\XmlFile;
use MagentoPerfRules\Xml\XmlRule;
use MagentoPerfRules\Xml\XPath;

/**
 * X-SEC-01: sections.xml declarations that make the browser reload all customer data
 * sections, or reload sections on every POST.
 */
final class SectionInvalidationRule implements XmlRule
{
    public const ID = 'X-SEC-01';

    /**
     * Actions that change the customer, store or currency, which core itself configures to
     * reload all sections (Magento_Customer, Magento_Store, Magento_Directory sections.xml).
     */
    private const CORE_ALL_SECTION_ACTIONS = [
        'customer/account/logout',
        'customer/account/loginpost',
        'customer/account/createpost',
        'customer/account/editpost',
        'stores/store/switch',
        'stores/store/switchrequest',
        'directory/currency/switch',
    ];

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_CONFIG && basename($file->relativePath) === 'sections.xml';
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach (XPath::elements($document, '/config/action') as $action) {
            $name = $action->getAttribute('name');
            $sectionNames = array_map(static fn ($s): string => $s->getAttribute('name'), XPath::elements($document, 'section', $action));
            // Core reloads only the "messages" section after every POST to show flash messages.
            if ($name === '*' && $sectionNames === ['messages']) {
                continue;
            }
            if ($name === '*') {
                $findings[] = new Finding(self::ID, Severity::Error, $file->relativePath, $action->getLineNo(), sprintf(
                    '[%s] <action name="*"> invalidates customer data sections on every POST request. List the actions that change the data.',
                    self::ID,
                ), $action);
                continue;
            }
            $sections = XPath::elements($document, 'section', $action);
            $all = $sections === [] || in_array('*', array_map(static fn ($s): string => $s->getAttribute('name'), $sections), true);
            if ($all && !in_array(strtolower($name), self::CORE_ALL_SECTION_ACTIONS, true)) {
                $findings[] = new Finding(self::ID, Severity::Warning, $file->relativePath, $action->getLineNo(), sprintf(
                    '[%s] Action "%s" invalidates all customer data sections, so the browser reloads every section after it. '
                    . 'List only the sections this action changes.',
                    self::ID,
                    $name,
                ), $action);
            }
        }

        return $findings;
    }
}
