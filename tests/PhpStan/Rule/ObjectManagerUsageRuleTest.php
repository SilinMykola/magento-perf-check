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
        ]);
    }
}
