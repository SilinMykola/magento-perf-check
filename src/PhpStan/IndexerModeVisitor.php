<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks every method call inside a method that also calls something named *isScheduled*,
 * such as IndexerInterface::isScheduled() or a helper around it.
 */
final class IndexerModeVisitor extends NodeVisitorAbstract
{
    public const MODE_CHECKED = 'magentoPerfIndexerModeChecked';

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
        if ($node instanceof Stmt\ClassMethod && $node->stmts !== null && $this->checksMode($node->stmts)) {
            $node->setAttribute(self::MODE_CHECKED, true);
            ++$this->depth;
        }
        if ($this->depth > 0 && $node instanceof Expr\MethodCall) {
            $node->setAttribute(self::MODE_CHECKED, true);
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node instanceof Stmt\ClassMethod && $node->getAttribute(self::MODE_CHECKED) === true) {
            --$this->depth;
        }

        return null;
    }

    /**
     * @param Stmt[] $statements
     */
    private function checksMode(array $statements): bool
    {
        return (new NodeFinder())->findFirst($statements, static fn (Node $n): bool => $n instanceof Expr\MethodCall
            && $n->name instanceof Node\Identifier
            && str_contains($n->name->toLowerString(), 'isscheduled')) !== null;
    }
}
