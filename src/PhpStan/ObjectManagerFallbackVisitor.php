<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks ObjectManager::getInstance() calls that are the backward-compatible
 * constructor fallback: "$dependency ?: ObjectManager::getInstance()->get(...)" or
 * the same with "??". Adobe documents this pattern as allowed.
 */
final class ObjectManagerFallbackVisitor extends NodeVisitorAbstract
{
    public const FALLBACK = 'magentoPerfObjectManagerFallback';

    public function enterNode(Node $node): ?Node
    {
        $fallback = match (true) {
            $node instanceof Expr\BinaryOp\Coalesce => $node->right,
            $node instanceof Expr\Ternary && $node->if === null => $node->else,
            default => null,
        };
        if ($fallback === null) {
            return null;
        }
        $expr = $fallback;
        while ($expr instanceof Expr\MethodCall) {
            $expr = $expr->var;
        }
        // Names are not resolved yet when this visitor runs, so match "...ObjectManager::getInstance()"
        // by its last segment; the rule checks the fully qualified name.
        if ($expr instanceof Expr\StaticCall
            && $expr->class instanceof Node\Name
            && $expr->name instanceof Node\Identifier
            && $expr->name->toLowerString() === 'getinstance'
            && strtolower($expr->class->getLast()) === 'objectmanager'
        ) {
            $expr->setAttribute(self::FALLBACK, true);
        }

        return null;
    }

    public static function isGetInstance(Expr\StaticCall $call): bool
    {
        return $call->class instanceof Node\Name
            && $call->name instanceof Node\Identifier
            && $call->name->toLowerString() === 'getinstance'
            && strtolower(ltrim($call->class->toString(), '\\')) === 'magento\framework\app\objectmanager';
    }
}
