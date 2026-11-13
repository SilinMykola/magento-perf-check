<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Xml\Rule;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\Rule\JqueryUiBundleRule;
use Silin\MagentoPerfCheck\Xml\Rule\JqXhrMigrateMethodRule;
use Silin\MagentoPerfCheck\Xml\XmlAnalyzer;
use SplFileInfo;

final class JqXhrMigrateMethodRuleTest extends TestCase
{
    private const PROJECT = __DIR__ . '/../../../Fixtures/Rules/U-JS-01/project';

    public function testReportsMigrateMethodsOnAjaxChainsInStorefrontAndAdminScripts(): void
    {
        $root = realpath(self::PROJECT);
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new JqXhrMigrateMethodRule()]))->analyze(['app/code', 'app/design']);
        $reported = array_map(static fn (Finding $f): string => $f->file . ':' . $f->line, $result->findings);
        sort($reported);

        // Not reported: Knockout observables, this.storage, a jQuery collection, comments, strings, chains that
        // use done, fail, always or then, a chain past then() or a method that may not return the jqXHR, jQuery under
        // another name, an optional call, and a copy of jQuery, also in another script block of a template. Reported: a call inside the arguments of another.
        self::assertSame([
            'app/code/Acme/Ajax/view/adminhtml/web/js/form.js:2',
            'app/code/Acme/Ajax/view/frontend/templates/inline.phtml:6',
            'app/code/Acme/Ajax/view/frontend/web/js/big-callback.js:3',
            'app/code/Acme/Ajax/view/frontend/web/js/big-callback.js:5',
            'app/code/Acme/Ajax/view/frontend/web/js/nested-call.js:7',
            'app/code/Acme/Ajax/view/frontend/web/js/storage-call.js:13',
            'app/code/Acme/Ajax/view/frontend/web/js/storage-call.js:16',
            'app/code/Acme/Ajax/view/frontend/web/js/storage-call.js:9',
            'app/design/adminhtml/Acme/backend/Magento_Backend/web/js/save.js:2',
            'app/design/frontend/Acme/shop/Magento_Review/web/js/process-reviews.js:10',
        ], $reported);
        self::assertStringContainsString('[U-JS-01] .complete() on the result of $.ajax(): jQuery 3 has no such method', implode("\n", array_map(static fn (Finding $f): string => $f->message, $result->findings)));
        self::assertStringContainsString('Use .always().', implode("\n", array_map(static fn (Finding $f): string => $f->message, $result->findings)));
    }

    public function testStorefrontRulesDoNotReadAdminScripts(): void
    {
        $root = realpath(self::PROJECT);
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new JqueryUiBundleRule()]))->analyze(['app/code', 'app/design']);

        // Admin scripts are not read for a storefront rule: only the seven storefront files.
        self::assertSame(7, $result->filesAnalyzed);
    }

    public function testAScriptThatHitsAPcreLimitKeepsItsFindingsAndGivesANotice(): void
    {
        $root = sys_get_temp_dir() . '/mpc-ujs-' . bin2hex(random_bytes(4));
        $js = $root . '/app/code/Acme/Deep/view/frontend/web/js';
        mkdir($js, 0777, true);
        mkdir($root . '/app/code/Acme/Deep/etc');
        file_put_contents($root . '/app/code/Acme/Deep/etc/module.xml', '<?xml version="1.0"?><config><module name="Acme_Deep"/></config>');
        // Brackets nested 100000 deep exceed the PCRE recursion limit, or the JIT stack, by default.
        file_put_contents($js . '/deep.js', "$.get(u).success(f);\n$.ajax" . str_repeat('(', 100000) . str_repeat(')', 100000) . ".success(f);\n");
        try {
            $result = (new XmlAnalyzer(new FileDiscovery($root), [new JqXhrMigrateMethodRule()]))->analyze(['app/code']);
        } finally {
            $entries = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            /** @var SplFileInfo $entry */
            foreach ($entries as $entry) {
                $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
            }
            rmdir($root);
        }

        // The finding made before the limit is kept.
        self::assertSame(['app/code/Acme/Deep/view/frontend/web/js/deep.js:1'], array_map(static fn (Finding $f): string => $f->file . ':' . $f->line, $result->findings));
        self::assertCount(1, $result->notices);
        self::assertSame('script', $result->notices[0]->source);
        self::assertStringContainsString('U-JS-01 could not read all of this script', $result->notices[0]->message);
    }
}
