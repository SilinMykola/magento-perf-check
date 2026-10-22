<?php

declare(strict_types=1);

namespace MagentoPerfRules\Tests\PhpStan;

use MagentoPerfRules\PhpStan\LoopContextVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Test double: reports every method call that the visitor marked as in-loop.
 *
 * @implements Rule<MethodCall>
 */
final class LoopContextReportingRule implements Rule
{
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->getAttribute(LoopContextVisitor::IN_LOOP) !== true || !$node->name instanceof Node\Identifier) {
            return [];
        }

        return [
            RuleErrorBuilder::message('in loop: ' . $node->name->toString())
                ->identifier('magentoPerfTest.inLoop')
                ->build(),
        ];
    }
}
