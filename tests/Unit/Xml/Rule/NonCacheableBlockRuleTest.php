<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Xml\Rule;

use Silin\MagentoPerfCheck\Report\Finding;
use Silin\MagentoPerfCheck\Xml\FileDiscovery;
use Silin\MagentoPerfCheck\Xml\Rule\NonCacheableBlockRule;
use Silin\MagentoPerfCheck\Xml\XmlAnalyzer;
use PHPUnit\Framework\TestCase;

final class NonCacheableBlockRuleTest extends TestCase
{
    private const FIXTURE = __DIR__ . '/../../../Fixtures/Rules/X-LAY-01';

    /**
     * @return list<Finding>
     */
    private function findings(): array
    {
        $root = realpath(self::FIXTURE . '/project');
        self::assertIsString($root);
        $result = (new XmlAnalyzer(new FileDiscovery($root), [new NonCacheableBlockRule()]))
            ->analyze(['app/code', 'app/design']);
        self::assertSame([], $result->notices);

        return $result->findings;
    }

    public function testMatchesGoldenFile(): void
    {
        $actual = array_map(
            static fn (Finding $f): array => ['file' => $f->file, 'line' => $f->line, 'severity' => $f->severity->value],
            $this->findings(),
        );
        usort($actual, static fn (array $a, array $b): int => [$a['file'], $a['line']] <=> [$b['file'], $b['line']]);

        $expected = json_decode((string) file_get_contents(self::FIXTURE . '/expected.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($expected, $actual);
    }

    public function testMessageNamesHandleAndBlock(): void
    {
        $default = array_values(array_filter(
            $this->findings(),
            static fn (Finding $f): bool => str_ends_with($f->file, 'frontend/layout/default.xml'),
        ));

        self::assertCount(1, $default);
        self::assertSame(NonCacheableBlockRule::SHOP_WIDE_ID, $default[0]->ruleId);
        self::assertStringStartsWith('[X-LAY-03]', $default[0]->message);
        self::assertStringContainsString('"promo.banner"', $default[0]->message);
        self::assertStringContainsString('"default"', $default[0]->message);
    }

    public function testOtherHandlesKeepTheirOwnRule(): void
    {
        $contact = array_values(array_filter(
            $this->findings(),
            static fn (Finding $f): bool => str_ends_with($f->file, 'contact_index_index.xml'),
        ));

        self::assertCount(1, $contact);
        self::assertSame(NonCacheableBlockRule::ID, $contact[0]->ruleId);
        self::assertStringStartsWith('[X-LAY-01]', $contact[0]->message);
    }
}
