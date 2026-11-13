<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Silin\MagentoPerfCheck\PhpStan\Rule\MonologAddMethodRule;

/**
 * @extends RuleTestCase<MonologAddMethodRule>
 */
final class MonologAddMethodRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MonologAddMethodRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testMonolog1MethodsOnAMonologLogger(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/monolog-add-method.php'], [
            [self::message('addError', 'MonologAddMethodFixture\\ShopLogger', 'error'), 22],
            // Missing from the logger, as in a project already on Monolog 2.
            [self::message('addWarning', 'MonologAddMethodFixture\\ShopLogger', 'warning'), 23],
            [self::message('err', 'MonologAddMethodFixture\\ShopLogger', 'error'), 24],
            [self::message('addInfo', 'MonologAddMethodFixture\\ShopLogger', 'info'), 25],
            [self::message('addInfo', 'MonologAddMethodFixture\\ShopLogger', 'info'), 26],
            [self::message('addInfo', 'Monolog\\Logger', 'info'), 55],
            // parent:: reaches Monolog's own method; self:: reaches the class's own one.
            [self::message('addError', 'Monolog\\Logger', 'error'), 65],
            [self::message('addInfo', 'MonologAddMethodFixture\\ParentCaller', 'info'), 67],
            [self::message('addInfo', 'MonologAddMethodFixture\\ParentCaller', 'info'), 68],
        ]);
    }

    private static function message(string $method, string $class, string $replacement): string
    {
        return sprintf(
            '[U-LOG-01] %s() on %s: Monolog 2, which Magento requires from 2.4.4, removed this Monolog 1 method, so from 2.4.4 '
            . 'the call throws "Call to undefined method". Use %s(), with the same arguments.',
            $method,
            $class,
            $replacement,
        );
    }
}
