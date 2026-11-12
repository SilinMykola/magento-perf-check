<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\Unit\Xml\Script;

use Silin\MagentoPerfCheck\Xml\Script\JsScanner;
use Silin\MagentoPerfCheck\Xml\Script\JsString;
use PHPUnit\Framework\TestCase;

final class JsScannerTest extends TestCase
{
    /**
     * @return list<string> "line value key|dep" for each string literal
     */
    private static function strings(string $source, int $firstLine = 1): array
    {
        return array_map(
            static fn (JsString $s): string => sprintf('%d %s %s', $s->line, $s->value, $s->isKey ? 'key' : 'dep'),
            JsScanner::scan($source, $firstLine)->strings,
        );
    }

    public function testObjectKeysFollowABraceOrAComma(): void
    {
        self::assertSame(
            ['1 a key', '1 x dep', '1 b key', '1 y dep', '1 c dep', '1 d dep', '1 e dep'],
            self::strings("o = {'a': 'x', \"b\": 'y'}; t = q ? 'c' : 'd'; switch (v) { case 'e': }"),
        );
    }

    /**
     * @return list<string> "value: path" for each string literal, the path joined by "."
     */
    private static function paths(string $source): array
    {
        return array_map(
            static fn (JsString $s): string => $s->value . ': ' . implode('.', $s->path),
            JsScanner::scan($source)->strings,
        );
    }

    public function testPropertyPaths(): void
    {
        self::assertSame(
            ['a: deps', 'b: shim', 'c: shim.b.deps', '*: map', 'e: map.*.d'],
            self::paths("var config = {deps: ['a'], shim: {'b': {deps: ['c']}}, map: {'*': {d: 'e'}}}"),
        );
        // A comma ends the property; arrays, calls and blocks add nothing to the path.
        self::assertSame(
            ['a: x', 'b: ', 'c: y', 'd: deps'],
            self::paths("o = {x: f('a'), 'b': 1}; g({y: ['c']}); (function () { var c = {deps: ['d']}; })();"),
        );
        // A key is not a property of itself, and a value after a ternary is no key.
        self::assertSame(
            ['k: o', 'v: o.k', 't: o.q', 'f: o.q'],
            self::paths("x = {o: {'k': 'v', q: c ? 't' : 'f'}}"),
        );
        // Braces in a template substitution and a closer that matches no opener leave the paths as they were.
        self::assertSame(
            ['a: t', 'b: u'],
            self::paths("x = {t: `\${ {n: 1}.n }` + 'a'}; y = {u: ) 'b'}"),
        );
        // A comma inside a substitution, and a key-like token inside an array, do not change the property around them.
        self::assertSame(['z: t'], self::paths("x = {t: `\${a, b}` + 'z'}"));
        self::assertSame(['v: p'], self::paths("x = {p: [1, q: 'v']}"));
        // A template literal has a path too, and a comment between a key and its colon keeps it a key.
        self::assertSame(['t: deps'], self::paths('c = {deps: [`t`]}'));
        self::assertSame(['a: deps', 'b: deps', 'deps: ', 'c: deps'], self::paths("c = {deps /* x */ : ['a']}; d = {deps // x\n: ['b']}; e = {'deps' /* x */ : ['c']}"));
    }

    public function testCommentsAreNotCode(): void
    {
        $scan = JsScanner::scan("// 'a'\n/* 'b'\n'c' */ 'd'");

        self::assertSame(['3 d dep'], self::strings("// 'a'\n/* 'b'\n'c' */ 'd'"));
        self::assertSame([[" 'a'", 1], [" 'b'\n'c' ", 3]], array_map(static fn ($c): array => [$c->text, $c->endLine], $scan->comments));
    }

    public function testRegularExpressionsAndDivisions(): void
    {
        // A quote inside a regular expression literal does not start a string.
        self::assertSame(['1 a dep'], self::strings("x = /'\"[/]/.test(y) ? 'a' : 0"));
        self::assertSame(['1 a dep'], self::strings("function f() { return /'/; } 'a'"));
        // A "/" inside a character class does not end the regular expression.
        self::assertSame(['1 a dep'], self::strings("x = /[/'\"]/.test(y); r = 'a'"));
        // After a value, "/" divides.
        self::assertSame(['1 a dep'], self::strings("r = 4 / 2 + (n) / 3, s = 'a'"));
    }

    public function testLinesEscapesAndTemplates(): void
    {
        self::assertSame(
            ['5 a/b dep', '6 two' . "\n" . 'lines dep', '8 c dep'],
            self::strings("'a\\/b'\n`two\nlines`\n'c'", 5),
        );
        // An unterminated string ends at the line break.
        self::assertSame(['1 open dep', '2 next dep'], self::strings("'open\n'next'"));
    }

    public function testTokensThatEndAValue(): void
    {
        // Postfix "++", a keyword as a property name and a non-ASCII identifier are values: "/" after them divides.
        self::assertSame(['1 a dep'], self::strings("x = n++ / 2; r = 'a'"));
        self::assertSame(['1 a dep'], self::strings("x = o.return / 2; r = 'a'"));
        self::assertSame(['1 a dep'], self::strings("x = é / 2; r = 'a'"));
        // A byte order mark and a no-break space are white space: "/" after them starts a regular expression.
        self::assertSame(['1 a dep'], self::strings("\u{FEFF}/'/.test(b); r = 'a'"));
        self::assertSame(['1 a dep'], self::strings("x =\u{A0}/'/.test(b); r = 'a'"));
    }

    public function testTemplateSubstitutionsAreCode(): void
    {
        self::assertSame(['1 ` dep', '1 a dep'], self::strings('t = `${"`"}`; r = \'a\''));
        self::assertSame(['1 jquery/ui dep'], self::strings('`${require(\'jquery/ui\')}`'));
        // Braces inside a substitution do not close it.
        self::assertSame(['1 b dep', '2 c dep'], self::strings("`x\${ {a: 'b'}.a }\ny`; 'c'"));
        // A part of a template with substitutions is not a whole value; a template without them is.
        self::assertSame(['2 jquery/ui dep'], self::strings('`${base}jquery/ui`; `jquery/ui${x}`;' . "\n" . '`jquery/ui`'));
    }

    public function testEscapesAreDecoded(): void
    {
        self::assertSame(['1 /A' . "\n" . "\u{1F600}x dep"], self::strings('\'/\x41\n\u{1F600}\x\''));
        // A backslash before a line break continues the string.
        self::assertSame(['1 ab dep', '3 c dep'], self::strings("'a\\\nb'\n'c'"));
        self::assertSame(['1 ab dep', '3 c dep'], self::strings("'a\\\r\nb'\n'c'"));
    }
}
