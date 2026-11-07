<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\HotRenderPluginRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<HotRenderPluginRule>
 */
final class HotRenderPluginRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new HotRenderPluginRule(self::getContainer()->getByType(ProjectIndexProvider::class));
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testFixture(): void
    {
        $message = static fn (string $method, string $target): string => sprintf(
            '[X-PLG-02] %s is a plugin on %s, so it runs for every rendered block or layout element of every page. '
            . 'Put it on the specific class it is meant for, or keep it very cheap.',
            $method,
            $target,
        );

        $this->analyse([__DIR__ . '/../Fixtures/hot-render-plugin.php'], [
            [$message('Acme\Render\OnBlock::afterToHtml()', 'magento\framework\view\element\abstractblock'), 10],
            [$message('Acme\Render\OnBlock::beforeGetChildHtml()', 'magento\framework\view\element\abstractblock'), 15],
            [$message('Acme\Render\OnTemplate::aroundFetchView()', 'magento\framework\view\element\template'), 33],
            [$message('Acme\Render\OnLayout::beforeRenderElement()', 'magento\framework\view\layout'), 47],
        ]);
    }
}
