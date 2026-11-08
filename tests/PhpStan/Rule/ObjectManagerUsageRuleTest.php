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
        ]);
    }
}
