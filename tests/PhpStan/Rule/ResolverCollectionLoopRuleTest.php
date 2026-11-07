<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\ResolverCollectionLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ResolverCollectionLoopRule>
 */
final class ResolverCollectionLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ResolverCollectionLoopRule($this->createReflectionProvider(), self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/resolver-query.php'], [
            [
                '[X-GQL-02] foreach over Magento\Framework\Data\Collection\AbstractDb runs a query in the resolver of ProductInterface.brand_rows, '
                . 'a field on a type returned inside a list: one query per list element. '
                . 'Implement BatchResolverInterface, or collect the ids and load them once in a deferred value.',
                58,
            ],
        ]);
    }
}
