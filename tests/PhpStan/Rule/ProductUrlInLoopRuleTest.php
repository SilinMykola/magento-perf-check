<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\ProductUrlInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ProductUrlInLoopRule>
 */
final class ProductUrlInLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ProductUrlInLoopRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $message = static fn (string $method): string => '[P-LOOP-04] ' . $method . '() builds a product URL inside a loop: one URL rewrite query per product without a request path. '
            . 'Call addUrlRewrite() on the product collection.';

        $this->analyse([__DIR__ . '/../Fixtures/catalog-in-loop.php'], [
            [$message('getProductUrl'), 19],
            [$message('getUrlInStore'), 20],
            [$message('getUrl'), 21],
            [$message('getProductUrl'), 22],
            [$message('getUrlInStore'), 23],
            [$message('getProductUrl'), 26],
            [$message('getUrlInStore'), 29],
            [$message('getProductUrl'), 143],
        ]);
    }
}
