<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\CollectionCountRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CollectionCountRule>
 */
final class CollectionCountRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CollectionCountRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testReportsCountBeforeTheCollectionIsLoaded(): void
    {
        $suffix = ' on Magento\Catalog\Model\ResourceModel\Product\Collection loads every row of the collection to count it. Use getSize(), which runs a COUNT query.';

        $this->analyse([__DIR__ . '/../Fixtures/collection-count.php'], [
            ['[P-COL-01] count()' . $suffix, 13],
            ['[P-COL-01] sizeof()' . $suffix, 35],
            // The first of two counts loads the rows only to count them; the second is free.
            ['[P-COL-01] count()' . $suffix, 60],
        ]);
    }
}
