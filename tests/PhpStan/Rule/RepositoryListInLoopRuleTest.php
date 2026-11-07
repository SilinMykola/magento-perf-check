<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\RepositoryListInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<RepositoryListInLoopRule>
 */
final class RepositoryListInLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new RepositoryListInLoopRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/batch-queries-in-loop.php'], [
            ['[P-LOOP-09] Magento\Catalog\Api\ProductRepositoryInterface::getList() is called once per item of a loop: at least one query per iteration. Collect the values first and make one getList() with an "in" filter.', 37],
            ['[P-LOOP-09] Magento\Catalog\Api\ProductRepositoryInterface::getList() is called once per item of a loop: at least one query per iteration. Collect the values first and make one getList() with an "in" filter.', 39],
        ]);
    }
}
