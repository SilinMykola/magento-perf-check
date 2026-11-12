<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Xml\Rule;

use Silin\MagentoPerfCheck\Project\ProjectIndexBuilder;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\Rule\CacheIdFactorRule;
use Silin\MagentoPerfCheck\Xml\Rule\EntityHandleTtlRule;
use Silin\MagentoPerfCheck\Xml\Rule\GlobalAreaRule;
use Silin\MagentoPerfCheck\Xml\Rule\GlobalStylesheetRule;
use Silin\MagentoPerfCheck\Xml\Rule\HeadScriptRule;
use Silin\MagentoPerfCheck\Xml\Rule\IndexerWithoutMviewRule;
use Silin\MagentoPerfCheck\Xml\Rule\JqueryUiBundleRule;
use Silin\MagentoPerfCheck\Xml\Rule\NonCacheableBlockRule;
use Silin\MagentoPerfCheck\Xml\Rule\ObserverEventRule;
use Silin\MagentoPerfCheck\Xml\Rule\RedundantIndexRule;
use Silin\MagentoPerfCheck\Xml\Rule\RequireJsDepsRule;
use Silin\MagentoPerfCheck\Xml\Rule\SectionInvalidationRule;
use Silin\MagentoPerfCheck\Xml\Rule\SynchronousTopicRule;
use Silin\MagentoPerfCheck\Xml\Rule\TableWithoutPrimaryKeyRule;
use Silin\MagentoPerfCheck\Xml\XmlAnalyzer;
use Silin\MagentoPerfCheck\Xml\XmlFile;
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
            new RedundantIndexRule(),
            new GlobalAreaRule(),
            new CacheIdFactorRule(),
            new HeadScriptRule(),
            new GlobalStylesheetRule(),
            new JqueryUiBundleRule(),
            new RequireJsDepsRule(),
        ]))->analyze(['app/code', 'app/design']);

        self::assertSame([], $result->notices);
        $actual = array_map(
            static fn (Finding $f): string => sprintf('%s %s %s:%d', $f->ruleId, $f->severity->value, $f->file, $f->line),
            $result->findings,
        );
        sort($actual);
        $expected = array_values(array_filter(array_map('trim', (array) file(self::FIXTURE . '/expected.txt')), static fn (string $l): bool => $l !== ''));

        self::assertSame($expected, $actual);
    }

    public function testLoadAfterMessagesNameWhatTriggersTheEvent(): void
    {
        $root = realpath(self::FIXTURE . '/project');
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [ObserverEventRule::loadAfter()]))->analyze(['app/code']);

        $messages = [];
        foreach ($result->findings as $finding) {
            $messages[$finding->line] = $finding->message;
        }

        self::assertStringContainsString('runs after loading any model, of any entity type', $messages[4] ?? '');
        self::assertStringContainsString('runs on every load of this entity type', $messages[8] ?? '');
        self::assertStringContainsString('runs after loading any non-EAV collection, of any entity type', $messages[26] ?? '');
    }

    public function testHeadScriptMessageNamesTheScriptAndTheHandle(): void
    {
        $root = realpath(self::FIXTURE . '/project');
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new HeadScriptRule()]))->analyze(['app/code']);

        $messages = array_map(static fn (Finding $f): string => $f->message, $result->findings);
        self::assertCount(5, $messages);
        $message = implode("\n", array_filter($messages, static fn (string $m): bool => str_contains($m, 'sdk.js')));
        self::assertStringContainsString('"https://example.com/sdk.js"', $message);
        self::assertStringContainsString('handle "catalog_product_view"', $message);
        self::assertStringContainsString('dev/js/defer_non_critical', $message);
    }

    public function testGlobalStylesheetMessageNamesTheStylesheetAndTheHandle(): void
    {
        $root = realpath(self::FIXTURE . '/project');
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new GlobalStylesheetRule()]))->analyze(['app/code', 'app/design']);

        $messages = array_map(static fn (Finding $f): string => $f->message, $result->findings);
        self::assertCount(15, $messages);
        $message = implode("\n", array_filter($messages, static fn (string $m): bool => str_contains($m, 'slider.css')));
        self::assertStringContainsString('"Acme_Rules::css/slider.css"', $message);
        self::assertStringContainsString('handle "default"', $message);
        self::assertStringContainsString('dev/css/use_css_critical_path', $message);
    }

    public function testJqueryUiMessageNamesTheModularWidgets(): void
    {
        $root = realpath(self::FIXTURE . '/project');
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new JqueryUiBundleRule()]))->analyze(['app/code/Acme/Rules/view/base']);

        self::assertCount(1, $result->findings);
        self::assertStringContainsString('jquery/compat', $result->findings[0]->message);
        self::assertStringContainsString('"jquery-ui-modules/widget"', $result->findings[0]->message);
        self::assertStringContainsString('Magento 2.3.3', $result->findings[0]->message);
    }

    public function testRequireJsDepsMessageNamesTheModule(): void
    {
        $root = realpath(self::FIXTURE . '/project');
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new RequireJsDepsRule()]))->analyze(['app/code/Acme/Rules/view/frontend']);

        self::assertCount(2, $result->findings);
        self::assertStringContainsString('"Acme_Rules/js/everywhere"', $result->findings[0]->message);
        self::assertStringContainsString('every storefront page', $result->findings[0]->message);
        self::assertStringContainsString('x-magento-init', $result->findings[0]->message);
    }

    public function testConfiguredAllowlistPrefix(): void
    {
        $layout = static fn (string $handle): XmlFile => new XmlFile('/x.xml', 'x.xml', XmlFile::KIND_LAYOUT, 'frontend', 'Acme_Rules', $handle);

        self::assertFalse((new NonCacheableBlockRule(['acme_account_']))->appliesTo($layout('acme_account_index')));
        self::assertTrue((new NonCacheableBlockRule(['acme_account_']))->appliesTo($layout('contact_index_index')));
        // Replacing the list drops the defaults.
        self::assertTrue((new NonCacheableBlockRule(['acme_account_'], true))->appliesTo($layout('checkout_cart_index')));
        // A prefix covers the hyva_ copy of its handles, and a hyva_ prefix matches as written.
        self::assertFalse((new NonCacheableBlockRule(['acme_account_']))->appliesTo($layout('hyva_acme_account_index')));
        self::assertFalse((new NonCacheableBlockRule(['hyva_acme_'], true))->appliesTo($layout('hyva_acme_index')));
        self::assertTrue((new NonCacheableBlockRule(['customer_'], true))->appliesTo($layout('hyva_checkout_index_index')));
    }
}
