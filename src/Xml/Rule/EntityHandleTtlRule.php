<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Rule;

use DOMDocument;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Rule\Severity;
use Silin\MagentoPerfCheck\Xml\XmlFile;
use Silin\MagentoPerfCheck\Xml\XmlRule;
use Silin\MagentoPerfCheck\Xml\XPath;

/**
 * X-LAY-02: ttl (ESI) on a block in an entity-specific handle; core throws a LogicException.
 */
final class EntityHandleTtlRule implements XmlRule
{
    public const ID = 'X-LAY-02';

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_LAYOUT
            && in_array($file->area, ['frontend', 'base'], true)
            && $file->handle !== null
            && (str_contains($file->handle, '_id_') || str_contains($file->handle, '_sku_'));
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        foreach (XPath::elements($document, '//block[@ttl]') as $block) {
            $findings[] = new Finding(
                self::ID,
                Severity::Error,
                $file->relativePath,
                $block->getLineNo(),
                sprintf(
                    '[%s] Block "%s" has ttl="%s" in entity-specific handle "%s": with ESI enabled, core throws a LogicException. '
                    . 'Move the block to a shared handle or drop ttl.',
                    self::ID,
                    $block->getAttribute('name'),
                    $block->getAttribute('ttl'),
                    (string) $file->handle,
                ),
                $block,
            );
        }

        return $findings;
    }
}
