<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks every node inside a branch of an if statement with IN_CONDITIONAL. The condition of
 * the if is not marked, because it always runs; the condition of an elseif may not run, so it is.
 *
 * Marks every node in the fallback of `$a ?: $b` or `$a ?? $b`, the `$b` that runs only when `$a`
 * is empty, with IN_FALLBACK.
 */
final class ConditionalContextVisitor extends NodeVisitorAbstract
{
    public const IN_CONDITIONAL = 'magentoPerfInConditional';

    public const IN_FALLBACK = 'magentoPerfInFallback';

    private const BRANCH = 'magentoPerfConditionalBranch';

    private const FALLBACK = 'magentoPerfFallback';

    private int $depth = 0;

    private int $fallbackDepth = 0;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->depth = 0;
        $this->fallbackDepth = 0;

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node->getAttribute(self::BRANCH) === true) {
            ++$this->depth;
        }
        if ($this->depth > 0) {
            $node->setAttribute(self::IN_CONDITIONAL, true);
        }
        if ($node instanceof Stmt\If_) {
            foreach ([...$node->stmts, ...$node->elseifs, ...($node->else === null ? [] : [$node->else])] as $branch) {
                $branch->setAttribute(self::BRANCH, true);
            }
        }
        if ($node->getAttribute(self::FALLBACK) === true) {
            ++$this->fallbackDepth;
        }
        if ($this->fallbackDepth > 0) {
            $node->setAttribute(self::IN_FALLBACK, true);
        }
        if ($node instanceof Expr\BinaryOp\Coalesce) {
            $node->right->setAttribute(self::FALLBACK, true);
        } elseif ($node instanceof Expr\Ternary && $node->if === null) {
            $node->else->setAttribute(self::FALLBACK, true);
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node->getAttribute(self::BRANCH) === true) {
            --$this->depth;
        }
        if ($node->getAttribute(self::FALLBACK) === true) {
            --$this->fallbackDepth;
        }

        return null;
    }
}
