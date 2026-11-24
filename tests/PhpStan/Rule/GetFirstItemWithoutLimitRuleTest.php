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
            // A fixed unique column or addIdFilter() with a value that may be a list.
            [$message, 250],
            [$message, 252],
            [$message, 265],
            // The value variable assigned again before getFirstItem().
            [$message, 275],
            [$message, 278],
            // A value declared as one or many.
            [$message, 293],
            // A fixed unique column or addIdFilter() in a branch or an arrow function, on a new collection after another one,
            // or with a value reassigned by reference, destructuring, foreach or +=.
            [$message, 305],
            [$message, 309],
            [$message, 311],
            [$message, 322],
            [$message, 324],
            [$message, 326],
            [$message, 343],
            [$message, 346],
            [$message, 351],
            [$message, 356],
            [$message, 361],
            // A new collection after another one that was iterated, counted, compared by size or loaded.
            [$message, 368],
            [$message, 374],
            [$message, 381],
            [$message, 387],
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
            // A fixed unique column or addIdFilter() with a value that may be a list.
            [$message, 250],
            [$message, 252],
            [$message, 265],
            // The value variable assigned again before getFirstItem().
            [$message, 275],
            [$message, 278],
            // A value declared as one or many.
            [$message, 293],
            // A fixed unique column or addIdFilter() in a branch or an arrow function, on a new collection after another one,
            // or with a value reassigned by reference, destructuring, foreach or +=.
            [$message, 305],
            [$message, 309],
            [$message, 311],
            [$message, 322],
            [$message, 324],
            [$message, 326],
            [$message, 343],
            [$message, 346],
            [$message, 351],
            [$message, 356],
            [$message, 361],
            // A new collection after another one that was iterated, counted, compared by size or loaded.
            [$message, 368],
            [$message, 374],
            [$message, 381],
            [$message, 387],
        ]);
    }

    public function testFilterOnEveryColumnOfAUniqueKey(): void
    {
        $message = static fn (string $class): string => '[P-COL-02] Acme\\Tags\\Model\\ResourceModel\\' . $class . '\\Collection::getFirstItem() without setPageSize(): '
            . 'the query has no LIMIT and loads every matching row. Call setPageSize(1) before getFirstItem().';

        // Keys: etc/db_schema.xml and Setup/UpgradeSchema.php of the fixture module.
        $this->analyse([__DIR__ . '/../Fixtures/unique-keys/app/code/Acme/Tags/Model/Tags.php'], [
            // A new collection filtered on half of the key, after another one filtered on the other half.
            [$message('Order'), 103],
            // Part of the key, a non-equality, a joined table's column, plain indexes.
            [$message('Order'), 116],
            [$message('Order'), 126],
            [$message('Order'), 140],
            [$message('Stock'), 154],
            // A unique index that a later version drops.
            [$message('Source'), 171],
            [$message('Source'), 176],
            // A key filter in a branch that may not run, in the other branch, or with a joined table.
            [$message('Order'), 185],
            [$message('Order'), 209],
            [$message('Order'), 217],
            [$message('Order'), 222],
            [$message('Order'), 232],
            // A value that may be a list, which becomes IN (...).
            [$message('Order'), 312],
            [$message('Order'), 318],
            // A property set to a new collection after the filter.
            [$message('Order'), 341],
            // A key filter in an arrow function, ?:, ??, &&, a match arm, a case, a catch or a loop.
            [$message('Order'), 347],
            [$message('Order'), 353],
            [$message('Order'), 359],
            [$message('Order'), 365],
            [$message('Order'), 374],
            [$message('Order'), 384],
            [$message('Order'), 394],
            [$message('Order'), 402],
            // Another table through getSelect()->from() or union().
            [$message('Order'), 415],
            [$message('Order'), 421],
            // A _construct() that does not call the parent's _init().
            [$message('Order\\Archive'), 426],
            // A setup-script key on a table that db_schema.xml declares without it.
            [$message('Order'), 436],
            // Keys dropped in the same script, by a name that cannot be tied to columns.
            [$message('Queue'), 446],
            [$message('Queue'), 451],
            // A list variable reassigned to one value before getFirstItem().
            [$message('Order'), 458],
            // A _construct() that calls the parent's only in a branch.
            [$message('Order\\Conditional'), 463],
            // A setup-script key on a table that db_schema.xml declares with a primary key, in another case.
            [$message('Mixed'), 468],
        ]);
    }
}
