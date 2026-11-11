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
        foreach ($markup?->tags('img') ?? [] as ['attributes' => $attributes, 'line' => $line]) {
            if ((self::hasSize($attributes, 'width') && self::hasSize($attributes, 'height'))
                || TemplateMarkup::printsAttributes($attributes)
            ) {
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
