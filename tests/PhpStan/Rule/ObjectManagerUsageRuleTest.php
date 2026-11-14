<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\ObjectManagerUsageRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ObjectManagerUsageRule>
 */
final class ObjectManagerUsageRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ObjectManagerUsageRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/object-manager-usage.php'], [
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\Helper. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 20],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\WithoutFallback. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 35],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\IfFallbacks. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 71],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\IfFallbacks. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 74],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LocalObjectManagerUsedElsewhere. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 99],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\IfFallbackEdges. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 120],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\IfFallbackEdges. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 123],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\IfFallbackEdges. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 128],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LocalObjectManagerHandedOn. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 147],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackBlockWithWork. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 167],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LocalObjectManagerCaptured. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 180],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LocalObjectManagerKeptInBlock. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 193],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackBlockWithHiddenWork. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 211],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackBlockWithHiddenWork. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 212],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackBlockWithHiddenWork. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 216],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 261],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 264],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 267],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 271],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 274],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 277],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 280],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 283],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 286],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 287],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 291],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackOnLocal. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 309],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackNewForms. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 326],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 351],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 355],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 358],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 361],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 365],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 369],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 380],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 384],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 388],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 393],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 398],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 404],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\DataLazyGetters. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 409],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\TernaryFallbacks. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 442],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\TernaryFallbacks. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 443],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\TernaryFallbacks. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 448],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetterThroughLocal. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 472],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetterThroughLocal. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 476],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\LazyGetterThroughLocal. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 481],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackScope. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 502],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackScope. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 514],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackScope. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 515],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackScope. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 516],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackScope. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 517],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackScope. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 519],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackScopeEdges. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 555],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackScopeEdges. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 556],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackScopeEdges. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 557],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\FallbackScopeEdges. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 559],
            ['[P-DI-06] ObjectManager::getInstance() is used directly in ObjectManagerUsageFixture\\InstanceofFallback. Inject the dependency through the constructor (a factory or Proxy if it must be created lazily).', 572],
        ]);
    }
}
