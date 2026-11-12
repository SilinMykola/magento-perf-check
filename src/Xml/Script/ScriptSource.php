<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Xml\Script;

use Silin\MagentoPerfCheck\PhpStan\TemplateMarkup;

/**
 * The JavaScript of a storefront file: a whole .js file, or the bodies of the JavaScript <script>
 * elements of a .phtml template.
 */
final class ScriptSource
{
    /**
     * @param list<JsString> $strings
     * @param list<JsComment> $comments
     */
    private function __construct(
        public readonly array $strings,
        public readonly array $comments,
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

            return new self($scan->strings, $scan->comments);
        }
        $scripts = TemplateMarkup::scripts($path);
        if ($scripts === null) {
            return null;
        }
        $strings = [];
        $comments = [];
        foreach ($scripts as $script) {
            $scan = JsScanner::scan($script['body'], $script['line']);
            array_push($strings, ...$scan->strings);
            array_push($comments, ...$scan->comments);
        }

        return new self($strings, $comments);
    }
}
