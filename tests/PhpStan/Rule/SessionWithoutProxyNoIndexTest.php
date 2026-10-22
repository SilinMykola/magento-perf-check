<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan\Rule;

use MagentoPerfRules\PhpStan\ProjectIndexProvider;
use MagentoPerfRules\PhpStan\Rule\SessionWithoutProxyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * Without Magento configuration the index is empty and the rule must not guess.
 *
 * @extends RuleTestCase<SessionWithoutProxyRule>
 */
final class SessionWithoutProxyNoIndexTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new SessionWithoutProxyRule(self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/no-magento.neon'];
    }

    public function testNoMagentoConfigurationMeansNoFindings(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/session-without-proxy.php'], []);
    }
}
