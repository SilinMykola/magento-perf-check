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
 * X-GQL-03: each provider added to idFactorProviders of CacheIdCalculator multiplies the
 * GraphQL cache keys by the number of values it can return. The factors of core modules are
 * needed for correct content, so nothing can be done about them and they are left out.
 */
final class CacheIdFactorRule implements XmlRule
{
    public const ID = 'X-GQL-03';

    private const CALCULATOR = 'Magento\GraphQlCache\Model\CacheId\CacheIdCalculator';

    public function ruleId(): string
    {
        return self::ID;
    }

    public function appliesTo(XmlFile $file): bool
    {
        return $file->kind === XmlFile::KIND_CONFIG
            && basename($file->relativePath) === 'di.xml'
            && !str_starts_with((string) $file->module, 'Magento_');
    }

    public function check(XmlFile $file, DOMDocument $document): array
    {
        $findings = [];
        $path = sprintf("/config/type[@name='%s' or @name='\\%s']/arguments/argument[@name='idFactorProviders']/item", self::CALCULATOR, self::CALCULATOR);
        foreach (XPath::elements($document, $path) as $item) {
            $findings[] = new Finding(self::ID, Severity::Info, $file->relativePath, $item->getLineNo(), sprintf(
                '[%s] Cache id factor "%s" adds a GraphQL cache entry per value it returns. Make sure it has few possible values: '
                . 'too many unique keys lower the cache hit rate.',
                self::ID,
                $item->getAttribute('name'),
            ), $item);
        }

        return $findings;
    }
}
