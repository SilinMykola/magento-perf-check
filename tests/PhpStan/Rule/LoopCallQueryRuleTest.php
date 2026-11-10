<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Silin\MagentoPerfCheck\PhpStan\LoopCallCollector;
use Silin\MagentoPerfCheck\PhpStan\MemoizingMethodCollector;
use Silin\MagentoPerfCheck\PhpStan\QueryCallCollector;
use Silin\MagentoPerfCheck\PhpStan\Rule\LoopCallQueryRule;

/**
 * @extends RuleTestCase<LoopCallQueryRule>
 */
final class LoopCallQueryRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new LoopCallQueryRule();
    }

    protected function getCollectors(): array
    {
        return [
            new QueryCallCollector($this->createReflectionProvider()),
            new MemoizingMethodCollector(),
            new LoopCallCollector($this->createReflectionProvider()),
        ];
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testMethodsThatQueryCalledInLoops(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/loop-call-query.php', __DIR__ . '/../Fixtures/loop-call-query-trait.php'], [
            [self::message('Example::productName', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 272), 166],
            [self::message('StockReader::qty', 'Magento\\Catalog\\Model\\Product::load()', 33), 169],
            [self::message('Example::loadInto', 'Magento\\Catalog\\Model\\ResourceModel\\Product::load()', 277), 172],
            // getList() once per item of array_map(); the same call in a paging while is left out.
            [self::message('Example::search', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getList()', 282), 174],
            // The first query by line.
            [self::message('Example::twoQueries', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 287), 176],
            // Declared in the parent class.
            [self::message('Base::inherited', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 21), 179],
            // A plain property is not a cache per key.
            [self::message('Example::remember', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 309), 183],
            // The if tests another property than the one it assigns.
            [self::message('Example::rememberWhenEnabled', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 360), 187],
            // The method keeps another value; its own query is not kept.
            [self::message('Example::counted', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 318), 189],
            [self::message('Example::loadThenCache', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 323), 190],
            [self::message('Example::labelled', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 335), 191],
            // An if on the property that neither returns nor assigns it.
            [self::message('Example::reload', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 343), 193],
            // An append keeps nothing for a later call.
            [self::message('Example::log', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 366), 195],
            [self::message('Example::staticName', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 371), 196],
            // A nullsafe call.
            [self::message('StockReader::qty', 'Magento\\Catalog\\Model\\Product::load()', 33), 197],
            // Declared in a trait: the line is in the trait's file.
            [self::message('Example::traitName', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 11, 'loop-call-query-trait.php'), 199],
            // parent:: and a class name.
            [self::message('Base::inherited', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 21), 200],
            [self::message('Example::staticName', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 371), 201],
            // A query in a closure that the called method runs itself.
            [self::message('Example::namesOf', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 399), 203],
            // A lazy getter on a new object in each iteration still queries once per item.
            [self::message('OrderLine::product', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 108), 214],
            // A cache per key on an object from the item, built with new or by a factory.
            [self::message('CachedReader::name', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 94), 216],
            [self::message('CachedReader::name', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 94), 217],
            [self::message('CachedReader::name', 'Magento\\Catalog\\Api\\ProductRepositoryInterface::getById()', 94), 218],
        ]);
    }

    private static function message(string $method, string $query, int $line, string $file = 'loop-call-query.php'): string
    {
        return sprintf(
            '[P-LOOP-12] LoopCallQueryFixture\%s() is called inside a loop and runs a query on every call: it calls %s on line %d of %s. '
            . 'Fetch the data for all items before the loop, with a collection or getList(), and pass it in.',
            $method,
            $query,
            $line,
            $file,
        );
    }
}
