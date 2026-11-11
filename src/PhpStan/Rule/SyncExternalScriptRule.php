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
 * T-05: a <script src> in a storefront template without async, defer or type="module". The
 * browser stops parsing the page until the script is downloaded and run.
 *
 * @implements Rule<FileNode>
 */
final class SyncExternalScriptRule implements Rule
{
    public const ID = 'T-05';
    public const IDENTIFIER = 'magentoPerf.syncExternalScript';

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $markup = TemplateMarkup::isStorefrontTemplate($scope->getFile()) ? TemplateMarkup::fromFile($scope->getFile()) : null;
        $errors = [];
        foreach ($markup?->tags('script') ?? [] as ['attributes' => $attributes, 'line' => $line]) {
            $type = $attributes['type'] ?? null;
            if (!isset($attributes['src'])
                || TemplateMarkup::hasAttribute($attributes, 'async')
                || TemplateMarkup::hasAttribute($attributes, 'defer')
                // A bound or printed type may be "module", or not JavaScript at all.
                || TemplateMarkup::isBound($attributes, 'type')
                || ($type !== null && TemplateMarkup::isPrinted($type))
                || !TemplateMarkup::isJavaScriptType($type)
                || TemplateMarkup::printsAttributes($attributes)
            ) {
                continue;
            }
            $errors[] = RuleErrorBuilder::message(sprintf(
                '[%s] This <script src> has no async or defer: the browser stops parsing the page until the script is downloaded and run. '
                . 'Add defer, or load the script when it is needed.',
                self::ID,
            ))->identifier(self::IDENTIFIER)->line($line)->build();
        }

        return $errors;
    }
}
