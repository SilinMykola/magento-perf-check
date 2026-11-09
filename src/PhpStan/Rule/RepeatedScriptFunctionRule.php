<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * T-03: a named JavaScript function declared inside a <script> that a template renders once per
 * item: inside a PHP loop, or anywhere in a product list item template, which Hyvä renders once
 * per product. Every item sends and parses the same function again.
 *
 * @implements Rule<FileNode>
 */
final class RepeatedScriptFunctionRule implements Rule
{
    public const ID = 'T-03';
    public const IDENTIFIER = 'magentoPerf.repeatedScriptFunction';

    private const ITEM_TEMPLATE = '/templates/product/list/item.phtml';

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $file = str_replace('\\', '/', $scope->getFile());
        if (!str_ends_with($file, '.phtml')) {
            return [];
        }
        $finder = new NodeFinder();
        $perItem = str_ends_with($file, self::ITEM_TEMPLATE);
        $inLoop = [];
        $loops = $finder->find($node->getNodes(), static fn (Node $n): bool => $n instanceof Stmt\Foreach_
            || $n instanceof Stmt\For_ || $n instanceof Stmt\While_ || $n instanceof Stmt\Do_);
        foreach ($loops as $loop) {
            foreach ($finder->findInstanceOf([$loop], Stmt\InlineHTML::class) as $html) {
                $inLoop[spl_object_id($html)] = true;
            }
        }

        $errors = [];
        $inScript = false;
        // InlineHTML nodes come in source order, so a <script> opened in one part stays open in the next.
        foreach ($finder->findInstanceOf($node->getNodes(), Stmt\InlineHTML::class) as $html) {
            $repeated = $perItem || isset($inLoop[spl_object_id($html)]);
            foreach (preg_split('/(<script\b[^>]*>|<\/script\s*>)/i', $html->value, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE) ?: [] as [$part, $offset]) {
                if (preg_match('/^<script\b/i', $part) === 1) {
                    $inScript = true;
                    continue;
                }
                if (preg_match('/^<\/script/i', $part) === 1) {
                    $inScript = false;
                    continue;
                }
                if (!$inScript || !$repeated || preg_match_all('/\bfunction\s+([A-Za-z_$][\w$]*)\s*\(/', $part, $matches, PREG_OFFSET_CAPTURE) < 1) {
                    continue;
                }
                foreach ($matches[1] as [$name, $at]) {
                    $line = $html->getStartLine() + substr_count(substr($html->value, 0, $offset + $at), "\n");
                    $errors[] = RuleErrorBuilder::message(sprintf(
                        '[%s] The JavaScript function %s() is declared in a part of the template that is rendered once per item: '
                        . 'every item sends and parses it again. Declare it once, in a template rendered once per page.',
                        self::ID,
                        $name,
                    ))->identifier(self::IDENTIFIER)->line($line)->build();
                }
            }
        }

        return $errors;
    }
}
