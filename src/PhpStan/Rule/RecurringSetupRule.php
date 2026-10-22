<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * X-SETUP-01: heavy work in Setup\Recurring or Setup\RecurringData, which run on every
 * setup:upgrade.
 *
 * @implements Rule<Stmt\ClassMethod>
 */
final class RecurringSetupRule implements Rule
{
    public const ID = 'X-SETUP-01';
    public const IDENTIFIER = 'magentoPerf.heavyRecurringSetup';

    private const HEAVY_METHODS = ['load', 'getitems', 'getsize', 'save', 'reindexall', 'reindexlist', 'reindexrow'];

    public function getNodeType(): string
    {
        return Stmt\ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $scope->getClassReflection()?->getName() ?? '';
        if ($node->name->toLowerString() !== 'install' || $node->stmts === null
            || preg_match('/\\\\Setup\\\\(Recurring|RecurringData)$/', $class, $m) !== 1
        ) {
            return [];
        }
        $finder = new NodeFinder();
        foreach ($node->stmts as $statement) {
            $heavy = $finder->findFirst([$statement], static fn (Node $n): bool => $n instanceof Stmt\Foreach_
                || $n instanceof Stmt\For_
                || $n instanceof Stmt\While_
                || $n instanceof Stmt\Do_
                || ($n instanceof Expr\MethodCall && $n->name instanceof Node\Identifier
                    && in_array($n->name->toLowerString(), self::HEAVY_METHODS, true)));
            if ($heavy !== null) {
                return [
                    RuleErrorBuilder::message(sprintf(
                        '[%s] %s::install() does heavy work (loops, loading, saving or reindexing); it runs on every setup:upgrade. '
                        . 'Move one-time work to a data or schema patch.',
                        self::ID,
                        $m[1],
                    ))->identifier(self::IDENTIFIER)->line($heavy->getStartLine())->build(),
                ];
            }
        }

        return [];
    }
}
