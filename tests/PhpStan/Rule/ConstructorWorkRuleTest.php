<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan\Rule;

use MagentoPerfRules\PhpStan\Rule\ConstructorWorkRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ConstructorWorkRule>
 */
final class ConstructorWorkRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ConstructorWorkRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/constructor-work.php', __DIR__ . '/../Fixtures/composer-plugin/src/Plugin.php'], [
            ['[P-DI-02] The constructor of ConstructorWorkFixture\\QueryInConstructor does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.', 24],
            ['[P-DI-02] The constructor of ConstructorWorkFixture\\CallsInit does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.', 32],
            ['[P-DI-02] The constructor of ConstructorWorkFixture\\Dispatching does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.', 92],
        ]);
    }
}
