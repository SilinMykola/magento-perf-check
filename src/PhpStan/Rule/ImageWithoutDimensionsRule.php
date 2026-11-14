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
        foreach ($markup?->tags('img') ?? [] as ['attributes' => $attributes, 'line' => $line]) {
            if ((self::hasSize($attributes, 'width') && self::hasSize($attributes, 'height'))
                || TemplateMarkup::printsAttributes($attributes)
            ) {
                continue;
            }
            // CSS that fixes the box reserves the space too: a width or height attribute, then the rules of the
            // template's own <style> for the image, in order, then its style attribute.
            $sized = [];
            foreach (['width', 'height'] as $name) {
                if (self::hasSize($attributes, $name)) {
                    $sized[$name] = '1px';
                }
            }
            $rules ??= self::cssRules((string) $markup?->styleSheet());
            if (self::fixesBox([...$sized, ...self::cascade($attributes['class'] ?? '', $attributes['style'] ?? '', $rules)])) {
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
            $css = (string) preg_replace('/@[^{};]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/', '', $css, -1, $count);
        } while ($count > 0);
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
     * as ".faq img.group-image"; the ancestors are not checked, pseudo-classes and attribute selectors do not
     * count, and !important and specificity are not followed: a later rule wins.
     *
     * @param list<array{selectors: list<string>, declarations: array<string, string>}> $rules
     * @return array<string, string>
     */
    private static function cascade(string $class, string $style, array $rules): array
    {
        $classes = preg_split('/\s+/', trim($class), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $declarations = [];
        foreach ($rules as $rule) {
            foreach ($rule['selectors'] as $selector) {
                $parts = preg_split('/[\s>+~]+/', $selector, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $last = (string) end($parts);
                if (strcasecmp($last, 'img') === 0 || (preg_match('/^(?:img)?((?:\.[\w-]+)+)$/i', $last, $m) === 1
                    && array_diff(explode('.', ltrim($m[1], '.')), $classes) === [])
                ) {
                    $declarations = array_merge($declarations, $rule['declarations']);
                    break;
                }
            }
        }

        return array_merge($declarations, self::declarations($style));
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
