<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan\Rule;

use MagentoPerfRules\PhpStan\Rule\DispatchInConstructorRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DispatchInConstructorRule>
 */
final class DispatchInConstructorRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DispatchInConstructorRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/constructor-work.php'], [
            ['[P-DI-03] An event is dispatched in the constructor of ConstructorWorkFixture\\Dispatching: its observers run every time the object is created. Dispatch it from the method where the event happens.', 92],
        ]);
    }
}
