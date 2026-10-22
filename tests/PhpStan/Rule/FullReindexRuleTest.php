<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan\Rule;

use MagentoPerfRules\PhpStan\ProjectIndexProvider;
use MagentoPerfRules\PhpStan\Rule\FullReindexRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<FullReindexRule>
 */
final class FullReindexRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new FullReindexRule($this->createReflectionProvider(), self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/fpc.php'], [
            ['[P-FPC-10] reindexAll() in observer Acme\\Promo\\Observer\\ProductSaved rebuilds the whole index during a request. Reindex the changed IDs with reindexList()/reindexRow(), or leave it to the indexer cron.', 187],
            ['[P-FPC-10] reindexAll() in plugin Acme\\Promo\\Plugin\\CartPlugin rebuilds the whole index during a request. Reindex the changed IDs with reindexList()/reindexRow(), or leave it to the indexer cron.', 204],
        ]);
    }
}
