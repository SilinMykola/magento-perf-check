<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Project;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Silin\MagentoPerfCheck\Project\ConfigPhpValues;

final class ConfigPhpValuesTest extends TestCase
{
    /**
     * @return list<array{path: list<string>, value: string|int|float|bool|null, line: int}>
     */
    private static function read(string $code): array
    {
        $file = sys_get_temp_dir() . '/mpr-config-php-' . bin2hex(random_bytes(4)) . '.php';
        file_put_contents($file, $code);
        try {
            return ConfigPhpValues::read($file);
        } finally {
            unlink($file);
        }
    }

    /**
     * @param list<array{list<string>, string|int|float|bool|null}> $expected key path and value of each literal
     */
    #[DataProvider('sources')]
    public function testReadsLiteralValuesWithTheirKeys(string $code, array $expected): void
    {
        self::assertSame($expected, array_map(static fn (array $v): array => [$v['path'], $v['value']], self::read($code)));
    }

    /**
     * @return iterable<string, array{string, list<array{list<string>, string|int|float|bool|null}>}>
     */
    public static function sources(): iterable
    {
        yield 'nested short arrays' => [
            "<?php\nreturn ['system' => ['default' => ['dev' => ['js' => ['merge_files' => '1']]]]];",
            [[['system', 'default', 'dev', 'js', 'merge_files'], '1']],
        ];
        yield 'long arrays, as var_export writes them' => [
            "<?php\nreturn array (\n  'a' => array ('b' => 2, 'c' => 1.5),\n);",
            [[['a', 'b'], 2], [['a', 'c'], 1.5]],
        ];
        yield 'true, false and null in any case' => [
            "<?php\nreturn ['a' => TRUE, 'b' => false, 'c' => Null, 'd' => \\true];",
            [[['a'], true], [['b'], false], [['c'], null], [['d'], true]],
        ];
        yield 'escapes in quoted strings' => [
            "<?php\nreturn ['it\\'s' => 'a\\\\b', \"t\" => \"x\\ty\", 'u' => \"\\q\\101\\x42\\u{43}\\u{416}\\\$\", 'v' => b'0'];",
            [[["it's"], 'a\\b'], [['t'], "x\ty"], [['u'], '\\qABC' . "\u{416}" . '$'], [['v'], '0']],
        ];
        yield 'list elements take the next index' => [
            "<?php\nreturn ['a' => ['x', 'w', 5 => 'y', 'z']];",
            [[['a', '0'], 'x'], [['a', '1'], 'w'], [['a', '5'], 'y'], [['a', '6'], 'z']],
        ];
        yield 'the next index after keys PHP converts to integers' => [
            "<?php\nreturn ['a' => ['5' => 'x', 'y'], 'b' => [true => 'x', 'y'], 'c' => [1.5 => 'x', 'y'], 'd' => [-5 => 'x', 'y'], 'e' => ['05' => 'x', 'y']];",
            [
                [['a', '5'], 'x'], [['a', '6'], 'y'],
                [['b', '1'], 'x'], [['b', '2'], 'y'],
                [['c', '1'], 'x'], [['c', '2'], 'y'],
                [['d', '-5'], 'x'], [['d', '-4'], 'y'],
                [['e', '05'], 'x'], [['e', '0'], 'y'],
            ],
        ];
        yield 'a key given twice keeps its last value' => [
            "<?php\nreturn ['a' => ['b' => '1', 'b' => '0'], 'c' => ['d' => '1'], 'c' => ['e' => '2']];",
            [[['a', 'b'], '0'], [['c', 'e'], '2']],
        ];
        yield 'negative numbers, as var_export writes them' => [
            "<?php\nreturn ['a' => -1, 'b' => -2.5, 'c' => +1];",
            [[['a'], -1], [['b'], -2.5], [['c'], 1]],
        ];
        yield 'numbers in other notations' => [
            "<?php\nreturn ['a' => 0x1A, 'b' => 1_000, 'c' => 0o17, 'd' => 017, 'e' => 0b11, 'f' => 0xFFFFFFFFFFFFFFFFF, 'g' => 1e3];",
            [[['a'], 26], [['b'], 1000], [['c'], 15], [['d'], 15], [['e'], 3], [['f'], 295147905179352825855.0], [['g'], 1000.0]],
        ];
        yield 'values that are not literals are left out' => [
            "<?php\nreturn ['a' => \\Acme\\Flag::ON, 'b' => '1' . '', 'c' => sprintf('%s', '1'), 'd' => '1' ?: '', 'e' => '1'];",
            [[['e'], '1']],
        ];
        yield 'a key that is not a literal leaves out its element' => [
            "<?php\nreturn [\\Acme\\Key::NAME => ['merge_files' => '1'], 'b' => ['c' => '1']];",
            [[['b', 'c'], '1']],
        ];
        yield 'keys PHP converts' => [
            "<?php\nreturn [null => 'n', true => 't', 2.7 => 'f', '99999999999999999999' => 'big', 'x'];",
            [[[''], 'n'], [['1'], 't'], [['2'], 'f'], [['99999999999999999999'], 'big'], [['3'], 'x']],
        ];
        yield 'a return inside a function does not count' => [
            "<?php\nfunction f() { return ['x' => 1]; }\nreturn ['a' => 2];",
            [[['a'], 2]],
        ];
        yield 'an array that is part of an expression is not a value' => [
            "<?php\nreturn ['system' => ['default' => ['merge_files' => '1']]['x'] ?? [], 'b' => 1];",
            [[['b'], 1]],
        ];
        yield 'an attribute in a value does not close the array' => [
            "<?php\nreturn ['a' => ['k' => #[Pure] fn() => 1, 'b' => 2], 'c' => 3];",
            [[['a', 'b'], 2], [['c'], 3]],
        ];
        yield 'a closing tag ends the return' => [
            "<?php\nreturn ['a' => 1] ?>\n",
            [[['a'], 1]],
        ];
        yield 'a file without the final semicolon is not valid PHP' => [
            "<?php\nreturn ['a' => 1]",
            [],
        ];
        yield 'only the first return counts' => [
            "<?php\nreturn ['a' => 1];\nreturn ['b' => 2];",
            [[['a'], 1]],
        ];
        yield 'a stray bracket does not stop the walk' => [
            "<?php\nreturn ['a' => 1, ) 'b' => 2];",
            [[['a'], 1], [['b'], 2]],
        ];
        yield 'comments do not count' => [
            "<?php\n// return ['x' => 1];\nreturn [/* 'y' => 2, */ 'z' => 3];",
            [[['z'], 3]],
        ];
        yield 'a file that returns no array literal' => [
            "<?php\nreturn require __DIR__ . '/other.php';",
            [],
        ];
        yield 'a file that returns a literal, not an array' => [
            "<?php\nreturn '1';",
            [],
        ];
        yield 'a file that returns nothing' => [
            "<?php\n\$config = ['a' => 1];",
            [],
        ];
    }

    public function testGivesTheLineOfEachValue(): void
    {
        $values = self::read("<?php\nreturn [\n    'a' => [\n        'b' => '1',\n    ],\n    'c' => 0,\n];\n");

        self::assertSame([4, 6], array_column($values, 'line'));
    }

    public function testReadsALargeFileInLinearTime(): void
    {
        $code = "<?php\nreturn ['system' => ['default' => [\n";
        for ($i = 0; $i < 20000; ++$i) {
            $code .= "    'k{$i}' => '1',\n";
        }
        $started = microtime(true);
        $values = self::read($code . "]]];\n");

        self::assertCount(20000, $values);
        // Quadratic work over 20000 values took about 17 s; the linear walk takes a fraction of a second.
        self::assertLessThan(3.0, microtime(true) - $started);
    }

    public function testMissingFile(): void
    {
        self::assertSame([], ConfigPhpValues::read(sys_get_temp_dir() . '/mpr-config-php-missing/config.php'));
    }
}
