<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\DirectReindexRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DirectReindexRule>
 */
final class DirectReindexRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DirectReindexRule($this->createReflectionProvider(), self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testFixture(): void
    {
        $suffix = ' reindexes during the request, even in Update on Schedule mode. Check $indexer->isScheduled() first, or use the indexer\'s processor.';

        $this->analyse([__DIR__ . '/../Fixtures/direct-reindex.php'], [
            ['[P-IDX-01] reindexRow() in observer Acme\Promo\Observer\Unchecked' . $suffix, 18],
            ['[P-IDX-01] reindexList() in observer Acme\Promo\Observer\Unchecked' . $suffix, 19],
            // The check counts only in the same method.
            ['[P-IDX-01] reindexRow() in observer Acme\Promo\Observer\CheckedElsewhere' . $suffix, 50],
            ['[P-IDX-01] reindexRow() in controller Acme\Promo\Controller\Index\Reindex' . $suffix, 67],
            ['[P-IDX-01] reindexList() in plugin Acme\Promo\Plugin\CartPlugin' . $suffix, 122],
        ]);
    }
}
