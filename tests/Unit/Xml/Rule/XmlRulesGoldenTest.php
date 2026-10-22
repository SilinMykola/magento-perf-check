<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Xml\Rule;

use MagentoPerfRules\Project\ProjectIndexBuilder;
use MagentoPerfRules\Report\Finding;
use MagentoPerfRules\Xml\FileDiscovery;
use MagentoPerfRules\Xml\Rule\EntityHandleTtlRule;
use MagentoPerfRules\Xml\Rule\IndexerWithoutMviewRule;
use MagentoPerfRules\Xml\Rule\NonCacheableBlockRule;
use MagentoPerfRules\Xml\Rule\ObserverEventRule;
use MagentoPerfRules\Xml\Rule\SectionInvalidationRule;
use MagentoPerfRules\Xml\Rule\SynchronousTopicRule;
use MagentoPerfRules\Xml\Rule\TableWithoutPrimaryKeyRule;
use MagentoPerfRules\Xml\XmlAnalyzer;
use MagentoPerfRules\Xml\XmlFile;
use PHPUnit\Framework\TestCase;

/**
 * All XML configuration rules over one fixture project. Every positive and negative
 * scenario of their specs is a file or an element in tests/Fixtures/Rules/XML/project.
 */
final class XmlRulesGoldenTest extends TestCase
{
    private const FIXTURE = __DIR__ . '/../../../Fixtures/Rules/XML';

    public function testMatchesGoldenFile(): void
    {
        $root = realpath(self::FIXTURE . '/project');
        self::assertIsString($root);
        $index = (new ProjectIndexBuilder(sys_get_temp_dir() . '/mpr-xml-golden'))->build($root);

        $result = (new XmlAnalyzer(new FileDiscovery($root), [
            new NonCacheableBlockRule(),
            new EntityHandleTtlRule(),
            ObserverEventRule::loadAfter(),
            ObserverEventRule::genericModelEvents(),
            ObserverEventRule::renderEvents(),
            new SectionInvalidationRule(),
            new SynchronousTopicRule(),
            new IndexerWithoutMviewRule($index),
            new TableWithoutPrimaryKeyRule($index),
        ]))->analyze(['app/code']);

        self::assertSame([], $result->notices);
        $actual = array_map(
            static fn (Finding $f): string => sprintf('%s %s %s:%d', $f->ruleId, $f->severity->value, $f->file, $f->line),
            $result->findings,
        );
        sort($actual);
        $expected = array_values(array_filter(array_map('trim', (array) file(self::FIXTURE . '/expected.txt')), static fn (string $l): bool => $l !== ''));

        self::assertSame($expected, $actual);
    }

    public function testConfiguredAllowlistPrefix(): void
    {
        $layout = static fn (string $handle): XmlFile => new XmlFile('/x.xml', 'x.xml', XmlFile::KIND_LAYOUT, 'frontend', 'Acme_Rules', $handle);

        self::assertFalse((new NonCacheableBlockRule(['acme_account_']))->appliesTo($layout('acme_account_index')));
        self::assertTrue((new NonCacheableBlockRule(['acme_account_']))->appliesTo($layout('contact_index_index')));
        // Replacing the list drops the defaults.
        self::assertTrue((new NonCacheableBlockRule(['acme_account_'], true))->appliesTo($layout('checkout_cart_index')));
    }
}
