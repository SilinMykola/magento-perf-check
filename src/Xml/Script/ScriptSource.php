<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Script;

use Silin\MagentoPerfCheck\PhpStan\TemplateMarkup;

/**
 * The JavaScript of a file: a whole .js file, or the bodies of the JavaScript <script> elements of a
 * .phtml template.
 */
final class ScriptSource
{
    /** @var ?list<array{string, int}> */
    private ?array $code = null;

    /**
     * @param list<JsString> $strings
     * @param list<JsComment> $comments
     * @param list<array{string, int}> $scripts each script's source and first line, for code()
     */
    private function __construct(
        public readonly array $strings,
        public readonly array $comments,
        private readonly array $scripts = [],
    ) {
    }

    public static function fromFile(string $path): ?self
    {
        if (!str_ends_with($path, '.phtml')) {
            $source = @file_get_contents($path);
            if ($source === false) {
                return null;
            }
            $scan = JsScanner::scan($source);

            return new self($scan->strings, $scan->comments, [[$source, 1]]);
        }
        $scripts = TemplateMarkup::scripts($path);
        if ($scripts === null) {
            return null;
        }
        $strings = [];
        $comments = [];
        $sources = [];
        foreach ($scripts as $script) {
            $scan = JsScanner::scan($script['body'], $script['line']);
            array_push($strings, ...$scan->strings);
            array_push($comments, ...$scan->comments);
            $sources[] = [$script['body'], $script['line']];
        }

        return new self($strings, $comments, $sources);
    }

    /**
     * The code of each script (JsScanner::$code: comments, strings and regular expressions blanked, offsets
     * and lines kept) with its first line, built when a rule first asks for it.
     *
     * @return list<array{string, int}>
     */
    public function code(): array
    {
        return $this->code ??= array_map(
            static fn (array $script): array => [JsScanner::scan($script[0], $script[1], true)->code, $script[1]],
            $this->scripts,
        );
    }
}
