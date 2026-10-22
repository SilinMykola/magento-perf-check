<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\Unit\Config;

use MagentoPerfRules\Config\ConfigLoader;
use MagentoPerfRules\Rule\RuleRegistry;
use PHPUnit\Framework\TestCase;

/**
 * The configuration example in README.md must stay valid.
 */
final class ReadmeExampleTest extends TestCase
{
    public function testReadmeExampleLoads(): void
    {
        $readme = (string) file_get_contents(__DIR__ . '/../../../README.md');
        self::assertSame(1, preg_match('/```neon\n(.*?)```/s', $readme, $m));

        $file = tempnam(sys_get_temp_dir(), 'mpr-readme');
        self::assertIsString($file);
        file_put_contents($file, $m[1]);
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
