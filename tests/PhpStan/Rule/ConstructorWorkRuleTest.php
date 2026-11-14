<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\ConstructorWorkRule;
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
            ['[P-DI-02] The constructor of ConstructorWorkFixture\\CoalesceAssignWork does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.', 125],
            ['[P-DI-02] The constructor of ConstructorWorkFixture\\TernaryWithWork does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.', 152],
            ['[P-DI-02] The constructor of ConstructorWorkFixture\\FallbackWithWork does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.', 171],
            ['[P-DI-02] The constructor of ConstructorWorkFixture\\FallbackArgumentWork does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.', 181],
            ['[P-DI-02] The constructor of ConstructorWorkFixture\\TernaryConditionWork does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.', 196],
            ['[P-DI-02] The constructor of ConstructorWorkFixture\\FallbackOtherCall does work beyond assigning dependencies and validating arguments. Move it to the method that needs the result, so it does not run every time the object is created.', 211],
        ]);
    }
}
