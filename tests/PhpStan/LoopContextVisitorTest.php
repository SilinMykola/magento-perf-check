<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<LoopContextReportingRule>
 */
final class LoopContextVisitorTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new LoopContextReportingRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon'];
    }

    public function testMarksLoopBodiesAndIteratingCallbacksOnly(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/loop-context.php'], [
            ['in loop: inForeach', 15],
            ['in loop: inFor', 19],
            ['in loop: inWhile', 23],
            ['in loop: inDo', 28],
            ['in loop: inArrayMap', 32],
            ['in loop: inArrowFn', 35],
            ['in loop: inArrayWalk', 38],
            ['in loop: inCollectionWalk', 42],
        ]);
    }
}
