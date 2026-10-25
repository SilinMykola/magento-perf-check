<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Config;

use Silin\MagentoPerfCheck\Config\ConfigLoader;
use Silin\MagentoPerfCheck\Rule\RuleRegistry;
use PHPUnit\Framework\TestCase;

/**
 * The configuration example in docs/configuration.md must stay valid.
 */
final class ConfigurationExampleTest extends TestCase
{
    public function testExampleLoads(): void
    {
        $document = (string) file_get_contents(__DIR__ . '/../../../docs/configuration.md');
        self::assertSame(1, preg_match('/```neon\nmagentoPerf:\n(.*?)```/s', $document, $m));

        $file = tempnam(sys_get_temp_dir(), 'mpr-readme');
        self::assertIsString($file);
        file_put_contents($file, "magentoPerf:\n" . $m[1]);
        try {
            $config = (new ConfigLoader(new RuleRegistry()))->load($file);
        } finally {
            unlink($file);
        }

        self::assertSame(['app/code', 'app/design'], $config->paths);
        self::assertFalse($config->rules['P-COL-02']->enabled);
        self::assertSame(['acme_account_'], $config->allowlistHandles);
    }
}
