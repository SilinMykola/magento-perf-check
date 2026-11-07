<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks every node inside a branch of an if statement with IN_CONDITIONAL. The condition of
 * the if is not marked, because it always runs; the condition of an elseif may not run, so it is.
 */
final class ConditionalContextVisitor extends NodeVisitorAbstract
{
    public const IN_CONDITIONAL = 'magentoPerfInConditional';

    private const BRANCH = 'magentoPerfConditionalBranch';

    private int $depth = 0;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->depth = 0;

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

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node->getAttribute(self::BRANCH) === true) {
            --$this->depth;
        }

        return null;
    }
}
