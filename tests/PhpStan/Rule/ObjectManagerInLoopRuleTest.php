<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\ObjectManagerInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ObjectManagerInLoopRule>
 */
final class ObjectManagerInLoopRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ObjectManagerInLoopRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $suffix = '() is called inside a loop. Inject the dependency through the constructor, or a factory when each iteration needs a new object.';

        $this->analyse([__DIR__ . '/../Fixtures/object-manager-in-loop.php'], [
            ['[P-LOOP-11] ObjectManager::create' . $suffix, 22],
            ['[P-LOOP-11] ObjectManager::get' . $suffix, 23],
            ['[P-LOOP-11] ObjectManager::create' . $suffix, 25],
        ]);
    }
}
