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
 * X-MQ-01: a communication.xml topic that is synchronous by default.
 */
final class SynchronousTopicRule implements XmlRule
{
    public const ID = 'X-MQ-01';

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_CONFIG && basename($file->relativePath) === 'communication.xml';
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach (XPath::elements($document, '/config/topic') as $topic) {
            $declaresContract = $topic->hasAttribute('schema')
                || ($topic->hasAttribute('request') && $topic->hasAttribute('response'));
            if (!$declaresContract || strtolower($topic->getAttribute('is_synchronous')) === 'false') {
                continue;
            }
            $findings[] = new Finding(self::ID, Severity::Warning, $file->relativePath, $topic->getLineNo(), sprintf(
                '[%s] Topic "%s" is synchronous by default: on AMQP the publisher waits for the consumer\'s reply. '
                . 'Add is_synchronous="false" unless the caller needs the response.',
                self::ID,
                $topic->getAttribute('name'),
            ), $topic);
        }

        return $findings;
    }
}
