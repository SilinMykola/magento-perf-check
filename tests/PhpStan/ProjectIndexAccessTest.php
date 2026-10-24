<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan;

use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<IndexReportingRule>
 */
final class ProjectIndexAccessTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new IndexReportingRule(self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon', __DIR__ . '/Fixtures/index/indexed-project.neon'];
    }

    public function testRulesReadTheIndexOfTheConfiguredProject(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/index/anything.php'], [
            ['modules: 4, banner proxy: yes', 1],
        ]);
    }
}
