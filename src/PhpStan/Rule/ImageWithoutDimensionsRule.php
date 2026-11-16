<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Silin\MagentoPerfCheck\PhpStan\TemplateMarkup;

/**
 * T-06: an <img> in a storefront template without both width and height, written or bound. The
 * browser cannot reserve its space before it loads, so the page shifts when it arrives.
 *
 * @implements Rule<FileNode>
 */
final class ImageWithoutDimensionsRule implements Rule
{
    public const ID = 'T-06';
    public const IDENTIFIER = 'magentoPerf.imageWithoutDimensions';

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $markup = TemplateMarkup::isStorefrontTemplate($scope->getFile()) ? TemplateMarkup::fromFile($scope->getFile()) : null;
        $errors = [];
        $rules = null;
        $ancestors = $markup?->imageAncestors();
        foreach ($markup?->tags('img') ?? [] as ['attributes' => $attributes, 'line' => $line, 'offset' => $offset]) {
            if ((self::hasSize($attributes, 'width') && self::hasSize($attributes, 'height'))
                || TemplateMarkup::printsAttributes($attributes)
            ) {
                continue;
            }
            // CSS that fixes the box reserves the space too: a width or height attribute, then the rules of the
            // module's or theme's stylesheets, then those of the template's own <style>, in order, then its style
            // attribute.
            $sized = [];
            foreach (['width', 'height'] as $name) {
                if (self::hasSize($attributes, $name)) {
                    $sized[$name] = '1px';
                }
            }
            // A stylesheet of the module serves every template of it, so its rules count only where the elements
            // they name around the image are around it in this template too.
            $rules ??= [
                ...array_map(static fn (array $rule): array => [...$rule, 'ancestors' => true], self::stylesheetRulesOf($scope->getFile())),
                ...array_map(static fn (array $rule): array => [...$rule, 'ancestors' => false], self::cssRules((string) $markup?->styleSheet())),
            ];
            if (self::fixesBox([...$sized, ...self::cascade($attributes['class'] ?? '', $attributes['style'] ?? '', $rules, $ancestors === null ? null : ($ancestors[$offset] ?? []))])) {
                continue;
            }
            $errors[] = RuleErrorBuilder::message(sprintf(
                '[%s] This <img> has no width and height: the browser cannot reserve its space before it loads, so the page shifts when it arrives. '
                . 'Set both attributes to the image\'s size.',
                self::ID,
            ))->identifier(self::IDENTIFIER)->line($line)->build();
        }

        return $errors;
    }

    /** @var array<string, list<array{selectors: list<string>, declarations: array<string, string>}>> stylesheet rules by directory */
    private static array $stylesheets = [];

    /**
     * The CSS and LESS files that style a template: under web/css of its module's view/frontend and
     * view/base, or of the theme's module folder and the theme itself. Whether a layout loads a CSS file,
     * or a theme compiles a LESS one, is not checked. Their rules, parsed once per directory.
     *
     * @return list<array{selectors: list<string>, declarations: array<string, string>}>
     */
    private static function stylesheetRulesOf(string $template): array
    {
        $template = str_replace('\\', '/', $template);
        $directories = [];
        if (preg_match('#^(.*)/view/(?:frontend|base)/templates/#', $template, $m) === 1) {
            $directories = [$m[1] . '/view/base/web/css', $m[1] . '/view/frontend/web/css'];
        } elseif (preg_match('#^(.*/app/design/frontend/[^/]+/[^/]+)/(?:([A-Z][A-Za-z0-9]*_[A-Z][A-Za-z0-9]*)/)?templates/#', $template, $m) === 1) {
            $directories = [$m[1] . '/web/css', ...(($m[2] ?? '') !== '' ? [$m[1] . '/' . $m[2] . '/web/css'] : [])];
        }
        // A Hyvä theme builds its styles with Tailwind and does not compile the LESS of a module.
        $hyva = preg_match('#/hyva/|hyva[^/]*\.phtml$#i', $template) === 1;
        $rules = [];
        foreach ($directories as $directory) {
            $rules = [...$rules, ...(self::$stylesheets[$directory . ($hyva ? '#css' : '')] ??= self::cssRules(self::flatten(self::readStylesheets($directory, $hyva))))];
        }

        return $rules;
    }

    private static function readStylesheets(string $directory, bool $cssOnly): string
    {
        if (!is_dir($directory)) {
            return '';
        }
        $files = [];
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::LEAVES_ONLY, \RecursiveIteratorIterator::CATCH_GET_CHILD);
            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && preg_match($cssOnly ? '/\.css$/' : '/\.(css|less)$/', $file->getFilename()) === 1 && !str_ends_with($file->getFilename(), '.min.css')) {
                    $files[] = $file->getPathname();
                }
            }
        } catch (\UnexpectedValueException) {
            return '';
        }
        sort($files);
        $css = '';
        foreach ($files as $file) {
            $css .= (string) @file_get_contents($file) . "\n";
        }

        return $css;
    }

    /**
     * Nested LESS rules as flat CSS: ".a { img { width: 1px } }" becomes ".a img { width: 1px }", "&" joins
     * the parent, and a guard ("when (...)") is dropped. At-rules such as @media are left out with their blocks,
     * as are mixin calls, variables and comments. Plain CSS comes out the same.
     */
    private static function flatten(string $less): string
    {
        // A "//" comment starts a line or follows a declaration or a brace; "url(//cdn/x.png)" is no comment.
        $less = (string) preg_replace(['#/\*.*?\*/#s', '#(^|[;{}])([ \t]*)//[^\n]*#m'], ['', '$1$2'], $less);
        $out = '';
        /** @var list<list<string>> $selectorStack */
        $selectorStack = [];
        /** @var list<string> $declarationStack */
        $declarationStack = [];
        $buffer = '';
        $skip = 0;
        $length = strlen($less);
        for ($i = 0; $i < $length; ++$i) {
            $char = $less[$i];
            if ($char === '{') {
                $selector = trim($buffer);
                $buffer = '';
                if ($skip > 0 || str_starts_with($selector, '@') || $selector === '') {
                    ++$skip;
                    continue;
                }
                $parents = $selectorStack !== [] ? $selectorStack[count($selectorStack) - 1] : [''];
                $selectors = [];
                // A guard, as in Magento's "& when (@media-common = true)", only decides whether the block is compiled.
                $selector = trim((string) preg_replace('/\s+when\s*\(.*$/s', '', $selector));
                foreach (array_map('trim', explode(',', $selector)) as $child) {
                    foreach ($parents as $parent) {
                        $selectors[] = str_contains($child, '&') ? str_replace('&', $parent, $child) : trim($parent . ' ' . $child);
                    }
                }
                $selectorStack[] = $selectors;
                $declarationStack[] = '';
            } elseif ($char === '}') {
                if ($skip > 0) {
                    --$skip;
                    $buffer = '';
                    continue;
                }
                $selectors = array_pop($selectorStack);
                $declarations = (string) array_pop($declarationStack);
                $last = trim($buffer);
                $buffer = '';
                if ($selectors !== null) {
                    $declarations .= $last !== '' && !str_starts_with($last, '@') && !str_starts_with($last, '.') ? $last . ';' : '';
                    if ($declarations !== '') {
                        $out .= implode(', ', $selectors) . ' {' . $declarations . "}\n";
                    }
                }
            } elseif ($char === ';') {
                $declaration = trim($buffer);
                $buffer = '';
                // Variables, mixin calls and imports are no declarations of the rule.
                if ($skip === 0 && $declarationStack !== [] && $declaration !== '' && !str_starts_with($declaration, '@') && !str_starts_with($declaration, '.')) {
                    $declarationStack[count($declarationStack) - 1] .= $declaration . ';';
                }
            } else {
                $buffer .= $char;
            }
        }

        return $out;
    }

    /**
     * Whether the declarations fix the box: a height with a width or an aspect ratio, or a width with an aspect
     * ratio. Each is a length, a width may also be a percentage; "auto", other keywords and variables are not.
     *
     * @param array<string, string> $declarations
     */
    private static function fixesBox(array $declarations): bool
    {
        // A percentage width is known before the image loads; a percentage height depends on the parent.
        $length = static fn (string $name, string $percent): bool => preg_match(
            '/^(0|\d*\.?\d+(px|em|rem|vh|vw|vi|vb|vmin|vmax|svh|lvh|dvh|svw|lvw|dvw|cm|mm|in|pt|pc|ch|ex' . $percent . ')|calc\((?!.*var\()[^%]*\))$/',
            $declarations[$name] ?? '',
        ) === 1;
        $width = $length('width', '|%');
        $ratio = preg_match('#^\d*\.?\d+(\s*/\s*\d*\.?\d+)?$#', $declarations['aspect-ratio'] ?? '') === 1;

        return ($length('height', '') && ($width || $ratio)) || ($ratio && $width);
    }

    /**
     * @return array<string, string> property => value, lower case, without !important; a later one wins
     */
    private static function declarations(string $text): array
    {
        $declarations = [];
        foreach (explode(';', strtolower($text)) as $declaration) {
            [$name, $value] = array_map('trim', explode(':', $declaration, 2) + [1 => '']);
            if ($name !== '' && $value !== '') {
                $declarations[$name] = trim((string) preg_replace('/!\s*important$/', '', $value));
            }
        }

        return $declarations;
    }

    /**
     * Rules outside at-rules: a rule inside @media or @supports applies only sometimes.
     *
     * @return list<array{selectors: list<string>, declarations: array<string, string>}>
     */
    private static function cssRules(string $css): array
    {
        $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);
        do {
            // On a PCRE limit, as with an at-rule of tens of thousands of rules, flatten() drops the at-rules instead.
            $stripped = preg_replace('/@[^{};]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/', '', $css, -1, $count);
            $css = $stripped ?? self::flatten($css);
        } while ($stripped !== null && $count > 0);
        $rules = [];
        if (preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $matches, PREG_SET_ORDER) > 0) {
            foreach ($matches as [, $selectors, $declarations]) {
                $rules[] = ['selectors' => array_map('trim', explode(',', $selectors)), 'declarations' => self::declarations($declarations)];
            }
        }

        return $rules;
    }

    /**
     * The declarations that reach an image with these classes, in source order, then its style attribute. A rule
     * counts when a selector ends in "img" or in a compound of classes the image has, with or without "img", such
     * as ".faq img.group-image". A stylesheet's rule counts only when the elements it names before the image are
     * around it; a template's own rule needs no such elements. Pseudo-classes, ids and attribute selectors do not
     * count, and !important and specificity are not followed: a later rule wins.
     *
     * @param list<array{selectors: list<string>, declarations: array<string, string>, ancestors: bool}> $rules
     * @param ?list<array{tag: string, classes: list<string>}> $around the elements around the image, outermost first;
     *     null when they are not known
     * @return array<string, string>
     */
    private static function cascade(string $class, string $style, array $rules, ?array $around): array
    {
        $classes = preg_split('/\s+/', trim($class), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $declarations = [];
        foreach ($rules as $rule) {
            foreach ($rule['selectors'] as $selector) {
                $parts = preg_split('/[\s>+~]+/', $selector, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $last = (string) array_pop($parts);
                if ((strcasecmp($last, 'img') === 0 || (preg_match('/^(?:img)?((?:\.[\w-]+)+)$/i', $last, $m) === 1
                    && array_diff(explode('.', ltrim($m[1], '.')), $classes) === []))
                    && ($rule['ancestors'] ? self::surrounds($parts, $around) : self::onlyTagsAndClasses($parts))
                ) {
                    $declarations = array_merge($declarations, $rule['declarations']);
                    break;
                }
            }
        }

        return array_merge($declarations, self::declarations($style));
    }

    /**
     * Whether every compound is a tag name with classes, or "*": one with a pseudo-class, an id or an attribute, as in
     * "a:hover img", applies only sometimes. The template's own rules are not matched against the elements around
     * the image, but these still do not count.
     *
     * @param list<string> $compounds
     */
    private static function onlyTagsAndClasses(array $compounds): bool
    {
        foreach ($compounds as $compound) {
            if (preg_match('/^(?:\*|(?:[a-z][\w-]*)?(?:\.[\w-]+)*)$/i', $compound) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the compounds before the image's own in a selector, such as ".card" and "div.box" in
     * ".card div.box img", are elements around it, in that order; combinators count as descendants. A compound
     * that is not a tag name with classes, such as one with a pseudo-class or an attribute, never matches.
     *
     * @param list<string> $compounds
     * @param ?list<array{tag: string, classes: list<string>}> $around null when not known: then only a selector without
     *     ancestors matches
     */
    private static function surrounds(array $compounds, ?array $around): bool
    {
        if ($around === null) {
            return $compounds === [];
        }
        $at = 0;
        foreach ($compounds as $compound) {
            if (preg_match('/^([a-z][\w-]*)?((?:\.[\w-]+)*)$/i', $compound, $m) !== 1 || ($m[1] === '' && $m[2] === '')) {
                return false;
            }
            $tag = strtolower($m[1]);
            $needed = $m[2] !== '' ? explode('.', ltrim($m[2], '.')) : [];
            for (; $at < count($around); ++$at) {
                if (($tag === '' || $around[$at]['tag'] === $tag) && array_diff($needed, $around[$at]['classes']) === []) {
                    break;
                }
            }
            if ($at === count($around)) {
                return false;
            }
            ++$at;
        }

        return true;
    }

    /**
     * A bound attribute, or a written one whose value is a number or printed by PHP: an empty
     * width or one such as "auto" gives the browser no size.
     *
     * @param array<string, string> $attributes
     */
    private static function hasSize(array $attributes, string $name): bool
    {
        $value = $attributes[$name] ?? null;

        return TemplateMarkup::isBound($attributes, $name)
            || ($value !== null && (preg_match('/^\s*\d/', $value) === 1 || TemplateMarkup::isPrinted($value)));
    }
}
