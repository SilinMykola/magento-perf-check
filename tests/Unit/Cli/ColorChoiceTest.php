<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Cli;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\Cli\ColorChoice;

final class ColorChoiceTest extends TestCase
{
    /**
     * @return iterable<string, array{bool, string, bool, string|false, string|false, bool, bool}>
     */
    public static function cases(): iterable
    {
        yield 'terminal' => [true, 'auto', false, false, 'xterm-256color', true, true];
        yield 'empty NO_COLOR counts as unset' => [true, 'auto', false, '', 'xterm', true, true];
        yield 'NO_COLOR' => [false, 'auto', false, '1', 'xterm', true, true];
        yield 'pipe' => [false, 'auto', false, false, 'xterm', false, true];
        yield 'dumb terminal' => [false, 'auto', false, false, 'dumb', true, true];
        yield 'Windows console without escape sequences' => [false, 'auto', false, false, false, true, false];
        yield 'always, also with NO_COLOR' => [true, 'always', false, '1', 'dumb', false, false];
        yield 'never' => [false, 'never', false, false, 'xterm', true, true];
        yield 'always, but to a file' => [false, 'always', true, false, 'xterm', true, true];
    }

    #[DataProvider('cases')]
    public function testDecide(bool $expected, string $color, bool $toFile, string|false $noColor, string|false $term, bool $terminal, bool $vt100): void
    {
        self::assertSame($expected, ColorChoice::decide($color, $toFile, $noColor, $term, $terminal, $vt100));
    }
}
