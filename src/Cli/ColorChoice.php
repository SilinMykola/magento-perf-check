<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Cli;

/**
 * Whether the table report uses colors and OSC 8 links: --color=always and --color=never decide, and
 * with --color=auto only a terminal that can show them gets them. A report written to --output never does.
 */
final class ColorChoice
{
    /**
     * @param string|false $noColor the NO_COLOR variable (https://no-color.org): any non-empty value turns colors off
     * @param string|false $term the TERM variable: "dumb" cannot show them
     * @param bool $vt100 on Windows, whether the console handles escape sequences; true elsewhere
     */
    public static function decide(string $color, bool $toFile, string|false $noColor, string|false $term, bool $terminal, bool $vt100): bool
    {
        if ($toFile || $color === 'never') {
            return false;
        }
        if ($color === 'always') {
            return true;
        }

        return ($noColor === false || $noColor === '') && $term !== 'dumb' && $terminal && $vt100;
    }
}
