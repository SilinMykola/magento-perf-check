<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\CollectionUniqueKeys;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\GetFirstItemWithoutLimitRule;
use PHPStan\Parser\Parser;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<GetFirstItemWithoutLimitRule>
 */
final class GetFirstItemWithoutLimitRuleTest extends RuleTestCase
{
    private bool $reportUntyped = false;

    protected function getRule(): Rule
    {
        $parser = self::getContainer()->getService('currentPhpVersionSimpleDirectParser');
        self::assertInstanceOf(Parser::class, $parser);
        $uniqueKeys = new CollectionUniqueKeys($this->createReflectionProvider(), $parser, self::getContainer()->getByType(ProjectIndexProvider::class));

        return new GetFirstItemWithoutLimitRule($this->createReflectionProvider(), $uniqueKeys, $this->reportUntyped);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/unique-keys-project.neon'];
    }

    public function testReportsOnlyUnlimitedDbCollections(): void
    {
        $message = '[P-COL-02] Magento\Catalog\Model\ResourceModel\Product\Collection::getFirstItem() without setPageSize(): '
            . 'the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().';

        $this->analyse([__DIR__ . '/../Fixtures/get-first-item.php'], [
            [$message, 21],
            [$message, 26],
            [$message, 32],
            [$message, 37],
            [$message, 118],
            [$message, 133],
            [$message, 167],
            [$message, 174],
            [$message, 190],
            [$message, 197],
            [$message, 218],
            // Only the first of two: it loads the rows.
            [$message, 224],
        ]);
    }

    public function testCountsUntypedReceiversWhenEnabled(): void
    {
        $this->reportUntyped = true;
        $message = '[P-COL-02] Magento\Catalog\Model\ResourceModel\Product\Collection::getFirstItem() without setPageSize(): '
            . 'the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().';

        $this->analyse([__DIR__ . '/../Fixtures/get-first-item.php'], [
            [$message, 21],
            [$message, 26],
            [$message, 32],
            [$message, 37],
            ['[notice:P-COL-02] getFirstItem() on a receiver of unknown type', 75],
            [$message, 118],
            [$message, 133],
            [$message, 167],
            [$message, 174],
            [$message, 190],
            [$message, 197],
            [$message, 218],
            // Only the first of two: it loads the rows.
            [$message, 224],
        ]);
    }

    public function testFilterOnEveryColumnOfAUniqueKey(): void
    {
        $message = static fn (string $class): string => '[P-COL-02] Acme\\Tags\\Model\\ResourceModel\\' . $class . '\\Collection::getFirstItem() without setPageSize(): '
            . 'the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().';

        // Keys: etc/db_schema.xml and Setup/UpgradeSchema.php of the fixture module.
        $this->analyse([__DIR__ . '/../Fixtures/unique-keys/app/code/Acme/Tags/Model/Tags.php'], [
            // A new collection filtered on half of the key, after another one filtered on the other half.
            [$message('Order'), 93],
            // Part of the key, a non-equality, a joined table's column, plain indexes.
            [$message('Order'), 106],
            [$message('Order'), 116],
            [$message('Order'), 130],
            [$message('Stock'), 144],
            // A unique index that a later version drops.
            [$message('Source'), 161],
            [$message('Source'), 166],
        ]);
    }
}
