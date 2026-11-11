<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\Rule\SyncExternalScriptRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<SyncExternalScriptRule>
 */
final class SyncExternalScriptRuleTest extends RuleTestCase
{
    private const MESSAGE = '[T-05] This <script src> has no async or defer: the browser stops parsing the page until the script is downloaded and run. Add defer, or load the script when it is needed.';

    protected function getRule(): Rule
    {
        return new SyncExternalScriptRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testSynchronousScriptsInAStorefrontTemplate(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/frontend/templates/external-script.phtml'], [
            [self::MESSAGE, 5],
            [self::MESSAGE, 6],
            [self::MESSAGE, 18],
            [self::MESSAGE, 29],
            [self::MESSAGE, 33],
            [self::MESSAGE, 34],
        ]);
    }

    public function testAdminTemplateIsLeftOut(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/templates/view/adminhtml/templates/admin.phtml'], []);
    }
}
