<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitorAbstract;

/**
 * Marks every method call inside an execute() whose return statements all return a
 * redirect or forward. Varnish and the built-in page cache store only 200 and 404
 * responses, so such actions are never served from cache. The check is syntactic.
 */
final class ControllerResultVisitor extends NodeVisitorAbstract
{
    public const IN_REDIRECT_ONLY_EXECUTE = 'magentoPerfInRedirectOnlyExecute';

    private const REDIRECT_METHODS = ['setpath', 'seturl', 'setrefererurl', 'setrefererorbaseurl', '_redirect', '_forward', 'forward'];

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
        if ($node instanceof Stmt\ClassMethod && $node->name->toLowerString() === 'execute' && $node->stmts !== null
            && $this->returnsOnlyRedirects($node->stmts)
        ) {
            $node->setAttribute(self::IN_REDIRECT_ONLY_EXECUTE, true);
            ++$this->depth;
        }
        if ($this->depth > 0 && $node instanceof Expr\MethodCall) {
            $node->setAttribute(self::IN_REDIRECT_ONLY_EXECUTE, true);
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node instanceof Stmt\ClassMethod && $node->getAttribute(self::IN_REDIRECT_ONLY_EXECUTE) === true) {
            --$this->depth;
        }

        return null;
    }

    /**
     * @param Stmt[] $statements
     */
    private function returnsOnlyRedirects(array $statements): bool
    {
        $finder = new NodeFinder();
        $redirectVariables = [];
        foreach ($finder->findInstanceOf($statements, Expr\Assign::class) as $assign) {
            if ($assign->var instanceof Expr\Variable && is_string($assign->var->name) && $this->isRedirect($assign->expr, [])) {
                $redirectVariables[$assign->var->name] = true;
            }
        }
        $returns = array_filter(
            $finder->findInstanceOf($statements, Stmt\Return_::class),
            fn (Stmt\Return_ $return): bool => !$this->isInsideNestedFunction($statements, $return, $finder),
        );
        if ($returns === []) {
            return false;
        }
        foreach ($returns as $return) {
            if ($return->expr === null || !$this->isRedirect($return->expr, $redirectVariables)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, true> $redirectVariables
     */
    private function isRedirect(Expr $expr, array $redirectVariables): bool
    {
        if ($expr instanceof Expr\Variable && is_string($expr->name)) {
            return isset($redirectVariables[$expr->name]);
        }
        while ($expr instanceof Expr\MethodCall) {
            if ($expr->name instanceof Node\Identifier) {
                $name = $expr->name->toLowerString();
                if (in_array($name, self::REDIRECT_METHODS, true)) {
                    return true;
                }
                if ($name === 'create'
                    && (preg_match('/redirect|forward/i', $this->receiverName($expr->var)) === 1 || $this->createsRedirectType($expr))
                ) {
                    return true;
                }
            }
            $expr = $expr->var;
        }

        return $expr instanceof Expr\Variable && is_string($expr->name) && isset($redirectVariables[$expr->name]);
    }

    /**
     * resultFactory->create(ResultFactory::TYPE_REDIRECT) or TYPE_FORWARD, matched by constant name.
     */
    private function createsRedirectType(Expr\MethodCall $create): bool
    {
        $type = $create->getArgs()[0]->value ?? null;

        return $type instanceof Expr\ClassConstFetch && $type->name instanceof Node\Identifier
            && in_array($type->name->toString(), ['TYPE_REDIRECT', 'TYPE_FORWARD'], true);
    }

    private function receiverName(Expr $expr): string
    {
        if ($expr instanceof Expr\PropertyFetch && $expr->name instanceof Node\Identifier) {
            return $expr->name->toString();
        }
        if ($expr instanceof Expr\Variable && is_string($expr->name)) {
            return $expr->name;
        }

        return '';
    }

    /**
     * @param Stmt[] $statements
     */
    private function isInsideNestedFunction(array $statements, Stmt\Return_ $return, NodeFinder $finder): bool
    {
        foreach ($finder->find($statements, static fn (Node $n): bool => $n instanceof Expr\Closure || $n instanceof Stmt\Function_ || $n instanceof Stmt\Class_) as $nested) {
            if ($finder->findFirst([$nested], static fn (Node $n): bool => $n === $return) !== null) {
                return true;
            }
        }

        return false;
    }
}
