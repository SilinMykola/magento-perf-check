<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\UnusedViewModelRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UnusedViewModelRule>
 */
final class UnusedViewModelRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new UnusedViewModelRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testUnusedResultOfRequire(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/templates/unused-view-model.phtml'], [
            ['[T-04] $menuItems is set from $viewModels->require() but never used: the call still runs, and Hyvä adds the view model\'s cache tags to the page when it implements IdentityInterface. Remove the call.', 3],
            ['[T-04] $navigationItems is set from $viewModels->require() but never used: the call still runs, and Hyvä adds the view model\'s cache tags to the page when it implements IdentityInterface. Remove the call.', 13],
        ]);
    }
}
