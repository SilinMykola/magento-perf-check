<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan;

use Silin\MagentoPerfCheck\PhpStan\Rule\ModelLoadInLoopRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ModelLoadInLoopRule>
 */
final class FactoryCreateTypeExtensionTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ModelLoadInLoopRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon'];
    }

    public function testCreateOfAFactoryThatIsNotGeneratedYet(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/factory-create.php'], [
            ['[P-LOOP-01] Magento\Catalog\Model\Product::load() is called inside a loop: one database query per iteration. Fetch all items before the loop with a collection or getList().', 40],
        ]);
    }
}
