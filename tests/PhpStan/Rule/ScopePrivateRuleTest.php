<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan\Rule;

use MagentoPerfRules\PhpStan\Rule\ScopePrivateRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ScopePrivateRule>
 */
final class ScopePrivateRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ScopePrivateRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        // The stub of AbstractBlock declares the property itself and must not be reported.
        $this->analyse([__DIR__ . '/../Fixtures/fpc.php', __DIR__ . '/../Stubs/fpc.php'], [
            ['[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.', 11],
            ['[P-FPC-01] $_isScopePrivate is obsolete and does not work properly. Load private data through customer data sections instead.', 19],
        ]);
    }
}
