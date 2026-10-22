<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan\Rule;

use MagentoPerfRules\PhpStan\Rule\CacheCleanRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<CacheCleanRule>
 */
final class CacheCleanRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new CacheCleanRule($this->createReflectionProvider());
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testFixture(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/fpc.php'], [
            ['[P-FPC-09] cleanType(\'full_page\') empties the whole full page cache (with Varnish: a full purge). Clean the cache tags of the changed entities instead.', 181],
            ['[P-FPC-09] cleanType(\'full_page\') empties the whole full page cache (with Varnish: a full purge). Clean the cache tags of the changed entities instead.', 182],
            ['[P-FPC-09] clean() without tags cleans every cache frontend. Pass the cache tags of the changed entities.', 184],
            ['[P-FPC-09] clean() without tags cleans every cache frontend. Pass the cache tags of the changed entities.', 185],
        ]);
    }
}
