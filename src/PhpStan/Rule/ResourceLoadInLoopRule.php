<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\LoopContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\LoopExitVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-LOOP-10: a resource model load($model, $id) inside a loop runs one query per iteration.
 *
 * @implements Rule<MethodCall>
 */
final class ResourceLoadInLoopRule implements Rule
{
    public const ID = 'P-LOOP-10';
    public const IDENTIFIER = 'magentoPerf.loopResourceLoad';

    private readonly MagentoTypes $types;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->types = new MagentoTypes($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->getAttribute(LoopContextVisitor::IN_LOOP) !== true
            || $node->getAttribute(LoopExitVisitor::LEAVES_LOOP) === true
            || !$node->name instanceof Node\Identifier
            || $node->name->toLowerString() !== 'load'
        ) {
            return [];
        }
        $class = $this->types->resourceModel($scope->getType($node->var));
        if ($class === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s::load() is called inside a loop: one database query per iteration. '
                . 'Fetch all items before the loop with a collection or getList().',
                self::ID,
                $class,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
