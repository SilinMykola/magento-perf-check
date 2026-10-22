<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks getFirstItem() calls with LIMITED when a limit was set on the same
 * collection: earlier in the call chain, or in an earlier statement of the same
 * function on the same variable or $this property.
 *
 * Statements are visited in source order, so "earlier" means earlier in the file;
 * there is no control-flow analysis (a limit set inside an if still counts).
 */
final class CollectionLimitVisitor extends NodeVisitorAbstract
{
    public const LIMITED = 'magentoPerfCollectionLimited';

    /** @var list<array<string, true>> receivers with a limit, one frame per function */
    private array $frames = [];

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->frames = [[]];

        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof FunctionLike && !$node instanceof Expr\ArrowFunction) {
            $this->frames[] = [];

            return null;
        }
        if (!$node instanceof Expr\MethodCall || !$node->name instanceof Node\Identifier) {
            return null;
        }

        if ($this->isLimitCall($node)) {
            $key = $this->rootKey($node);
            if ($key !== null) {
                $this->frames[count($this->frames) - 1][$key] = true;
            }
        } elseif ($node->name->toLowerString() === 'getfirstitem') {
            $key = $this->rootKey($node);
            $limitedEarlier = $key !== null && isset($this->frames[count($this->frames) - 1][$key]);
            if ($limitedEarlier || $this->chainHasLimit($node->var)) {
                $node->setAttribute(self::LIMITED, true);
            }
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node instanceof FunctionLike && !$node instanceof Expr\ArrowFunction) {
            array_pop($this->frames);
        }

        return null;
    }

    /**
     * setPageSize(), or limit()/limitPage() on getSelect().
     */
    private function isLimitCall(Expr\MethodCall $call): bool
    {
        if (!$call->name instanceof Node\Identifier) {
            return false;
        }
        $name = $call->name->toLowerString();
        if ($name === 'setpagesize') {
            return true;
        }

        return ($name === 'limit' || $name === 'limitpage')
            && $call->var instanceof Expr\MethodCall
            && $call->var->name instanceof Node\Identifier
            && $call->var->name->toLowerString() === 'getselect';
    }

    private function chainHasLimit(Expr $expr): bool
    {
        while ($expr instanceof Expr\MethodCall) {
            if ($this->isLimitCall($expr)) {
                return true;
            }
            $expr = $expr->var;
        }

        return false;
    }

    /**
     * Identifies the collection a chain starts from: "$var" or "$this->prop".
     */
    private function rootKey(Expr\MethodCall $call): ?string
    {
        $expr = $call->var;
        while ($expr instanceof Expr\MethodCall) {
            $expr = $expr->var;
        }
        if ($expr instanceof Expr\Variable && is_string($expr->name)) {
            return '$' . $expr->name;
        }
        if ($expr instanceof Expr\PropertyFetch
            && $expr->var instanceof Expr\Variable
            && $expr->var->name === 'this'
            && $expr->name instanceof Node\Identifier
        ) {
            return '$this->' . $expr->name->toString();
        }

        return null;
    }
}
