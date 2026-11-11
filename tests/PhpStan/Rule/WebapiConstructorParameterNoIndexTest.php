<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\WebapiConstructorParameterRule;
use Silin\MagentoPerfCheck\PhpStan\WebapiConstructorCollector;

/**
 * Without Magento configuration there are no routes and no preferences.
 *
 * @extends RuleTestCase<WebapiConstructorParameterRule>
 */
final class WebapiConstructorParameterNoIndexTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new WebapiConstructorParameterRule(self::getContainer()->getByType(ProjectIndexProvider::class), $this->createReflectionProvider());
    }

    protected function getCollectors(): array
    {
        return [new WebapiConstructorCollector(self::getContainer()->getByType(FileTypeMapper::class), $this->createReflectionProvider())];
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/no-magento.neon'];
    }

    public function testNoMagentoConfigurationMeansNoFindings(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/webapi-constructor.php'], []);
    }
}
