<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use MagentoPerfRules\PhpStan\ProjectIndexProvider;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-PLG-01: an around plugin that calls $proceed once, unconditionally, with its own
 * arguments unchanged; a before and/or after plugin would do, and costs less.
 *
 * @implements Rule<Stmt\ClassMethod>
 */
final class AroundPluginRule implements Rule
{
    public const ID = 'P-PLG-01';
    public const IDENTIFIER = 'magentoPerf.unnecessaryAroundPlugin';

    public function __construct(private readonly ProjectIndexProvider $index)
    {
    }

    public function getNodeType(): string
    {
        return Stmt\ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $scope->getClassReflection();
        if (!$node->isPublic() || !str_starts_with($node->name->toLowerString(), 'around')
            || $node->stmts === null || count($node->params) < 2 || $class === null
            || !$this->isPluginClass($class->getName())
        ) {
            return [];
        }
        $proceed = $node->params[1]->var;
        if (!$proceed instanceof Expr\Variable || !is_string($proceed->name)) {
            return [];
        }

        $finder = new NodeFinder();
        $isProceedCall = static fn (Node $n): bool => $n instanceof Expr\FuncCall
            && $n->name instanceof Expr\Variable
            && $n->name->name === $proceed->name;
        $calls = $finder->find($node->stmts, $isProceedCall);
        if (count($calls) !== 1) {
            return [];
        }
        $call = $calls[0];
        if (!$call instanceof Expr\FuncCall) {
            return [];
        }
        if (!$this->isAtTopLevel($node->stmts, $call, $finder)
            || $this->returnsBefore($node->stmts, $call, $finder)
            || !$this->passesArgumentsThrough($node, $call)
            || $this->reassignsArguments($node, $call, $finder)
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s::%s() calls $%s once, unconditionally and with unchanged arguments. '
                . 'A before and/or after plugin does the same with less overhead.',
                self::ID,
                $class->getName(),
                $node->name->toString(),
                $proceed->name,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    private function isPluginClass(string $class): bool
    {
        if ($this->index->get()->isPluginClass($class)) {
            return true;
        }
        $short = substr($class, (int) strrpos($class, '\\') + ($class !== '' && str_contains($class, '\\') ? 1 : 0));

        return str_ends_with($short, 'Plugin');
    }

    /**
     * The call sits in a statement directly in the method body (expression or return),
     * not inside a closure in that statement.
     *
     * @param Stmt[] $statements
     */
    private function isAtTopLevel(array $statements, Expr\FuncCall $call, NodeFinder $finder): bool
    {
        foreach ($statements as $statement) {
            if ($finder->findFirst([$statement], static fn (Node $n): bool => $n === $call) === null) {
                continue;
            }
            if (!$statement instanceof Stmt\Expression && !$statement instanceof Stmt\Return_) {
                return false;
            }
            foreach ($finder->findInstanceOf([$statement], Expr\Closure::class) as $closure) {
                if ($finder->findFirst($closure->stmts, static fn (Node $n): bool => $n === $call) !== null) {
                    return false;
                }
            }
            foreach ($finder->findInstanceOf([$statement], Expr\ArrowFunction::class) as $arrow) {
                if ($finder->findFirst([$arrow->expr], static fn (Node $n): bool => $n === $call) !== null) {
                    return false;
                }
            }

            return !$this->isInConditionalBranch($statement, $call, $finder);
        }

        return false;
    }

    /**
     * The call is a branch of a ternary, or the right operand of &&, ||, and, or, ??, so
     * it may not run.
     */
    private function isInConditionalBranch(Stmt $statement, Expr\FuncCall $call, NodeFinder $finder): bool
    {
        $contains = static fn (?Node $branch): bool => $branch !== null
            && $finder->findFirst([$branch], static fn (Node $n): bool => $n === $call) !== null;
        foreach ($finder->find([$statement], static fn (Node $n): bool => $n instanceof Expr\Ternary || $n instanceof Expr\BinaryOp) as $expr) {
            if ($expr instanceof Expr\Ternary && ($contains($expr->if) || $contains($expr->else))) {
                return true;
            }
            if (($expr instanceof Expr\BinaryOp\BooleanAnd || $expr instanceof Expr\BinaryOp\BooleanOr
                || $expr instanceof Expr\BinaryOp\LogicalAnd || $expr instanceof Expr\BinaryOp\LogicalOr
                || $expr instanceof Expr\BinaryOp\Coalesce) && $contains($expr->right)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * A return reached before $proceed (e.g. "if (!$enabled) { return $x; }") skips the
     * original method conditionally, which only an around plugin can do.
     *
     * @param Stmt[] $statements
     */
    private function returnsBefore(array $statements, Expr\FuncCall $call, NodeFinder $finder): bool
    {
        $callStart = $call->getStartFilePos();
        foreach ($finder->findInstanceOf($statements, Stmt\Return_::class) as $return) {
            if ($return->getStartFilePos() < $callStart
                && $finder->findFirst([$return], static fn (Node $n): bool => $n === $call) === null
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * A parameter passed to $proceed but written to before the call ("$prices = f($prices)",
     * "$parts[0] = 'x'") is a changed argument. Setting a property on an object argument is
     * not: the same object is passed, as a before plugin would.
     */
    private function reassignsArguments(Stmt\ClassMethod $method, Expr\FuncCall $call, NodeFinder $finder): bool
    {
        $names = [];
        foreach (array_slice($method->params, 2) as $param) {
            if ($param->var instanceof Expr\Variable && is_string($param->var->name)) {
                $names[$param->var->name] = true;
            }
        }
        $callStart = $call->getStartFilePos();
        foreach ($finder->find((array) $method->stmts, static fn (Node $n): bool => $n instanceof Expr\Assign || $n instanceof Expr\AssignOp || $n instanceof Expr\AssignRef) as $assign) {
            // Only writes that finish before the call; "$group = $proceed($group)" writes the result.
            if ($assign->getEndFilePos() >= $callStart) {
                continue;
            }
            $target = $assign->var;
            while ($target instanceof Expr\ArrayDimFetch) {
                $target = $target->var;
            }
            if ($target instanceof Expr\Variable && is_string($target->name) && isset($names[$target->name])) {
                return true;
            }
        }

        return false;
    }

    private function passesArgumentsThrough(Stmt\ClassMethod $method, Expr\FuncCall $call): bool
    {
        $expected = array_slice($method->params, 2);
        $args = $call->getArgs();
        if (count($args) !== count($expected)) {
            return false;
        }
        foreach ($args as $i => $arg) {
            $param = $expected[$i];
            if (!$arg->value instanceof Expr\Variable || !$param->var instanceof Expr\Variable
                || $arg->value->name !== $param->var->name || $arg->unpack !== $param->variadic
            ) {
                return false;
            }
        }

        return true;
    }
}
