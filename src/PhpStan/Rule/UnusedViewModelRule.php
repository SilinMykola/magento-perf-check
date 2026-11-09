<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * T-04: a template assigns what Hyvä's $viewModels->require() gives, or a call made on it or on a
 * variable holding it, to a variable it never reads. The call still runs, and require() adds the view model's cache tags
 * to the page when it implements IdentityInterface.
 *
 * @implements Rule<FileNode>
 */
final class UnusedViewModelRule implements Rule
{
    public const ID = 'T-04';
    public const IDENTIFIER = 'magentoPerf.unusedViewModel';

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!str_ends_with($scope->getFile(), '.phtml')) {
            return [];
        }
        $finder = new NodeFinder();
        $reads = [];
        $targets = [];
        foreach ($finder->findInstanceOf($node->getNodes(), Expr\Assign::class) as $assign) {
            if ($assign->var instanceof Expr\Variable) {
                $targets[spl_object_id($assign->var)] = true;
            }
        }
        foreach ($finder->findInstanceOf($node->getNodes(), Expr\Variable::class) as $variable) {
            if (is_string($variable->name) && !isset($targets[spl_object_id($variable)])) {
                $reads[$variable->name] = true;
            }
        }

        $errors = [];
        // Variables holding a required view model, in source order: "$vm = $viewModels->require(...)".
        $required = [];
        foreach ($finder->findInstanceOf($node->getNodes(), Expr\Assign::class) as $assign) {
            if (!$assign->var instanceof Expr\Variable || !is_string($assign->var->name) || !self::comesFromRequire($assign->expr, $required)) {
                continue;
            }
            $required[$assign->var->name] = true;
            if (isset($reads[$assign->var->name])) {
                continue;
            }
            $errors[] = RuleErrorBuilder::message(sprintf(
                '[%s] $%s is set from $viewModels->require() but never used: the call still runs, and Hyvä adds the '
                . 'view model\'s cache tags to the page when it implements IdentityInterface. Remove the call.',
                self::ID,
                $assign->var->name,
            ))->identifier(self::IDENTIFIER)->line($assign->getStartLine())->build();
        }

        return $errors;
    }

    /**
     * "$viewModels->require(...)", or a call chain that starts with it or with a variable holding its result.
     *
     * @param array<string, true> $required
     */
    private static function comesFromRequire(Expr $expr, array $required): bool
    {
        while ($expr instanceof Expr\MethodCall) {
            if ($expr->name instanceof Node\Identifier && $expr->name->toLowerString() === 'require'
                && $expr->var instanceof Expr\Variable && $expr->var->name === 'viewModels'
            ) {
                return true;
            }
            $expr = $expr->var;
            if ($expr instanceof Expr\Variable && is_string($expr->name) && isset($required[$expr->name])) {
                return true;
            }
        }

        return false;
    }
}
