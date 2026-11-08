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
 * P-LOOP-02: a collection created and loaded within one loop iteration runs one
 * query per iteration.
 *
 * @implements Rule<MethodCall>
 */
final class CollectionInLoopRule implements Rule
{
    public const ID = 'P-LOOP-02';
    public const IDENTIFIER = 'magentoPerf.loopCollectionLoad';

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
        if ($node->getAttribute(LoopContextVisitor::CREATED_IN_LOOP) !== true
            || $node->getAttribute(LoopExitVisitor::LEAVES_LOOP) === true
            || !$node->name instanceof Node\Identifier
        ) {
            return [];
        }
        $class = $this->types->subtypeOf($scope->getType($node->var), MagentoTypes::DB_COLLECTION);
        if ($class === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s is created and loaded (%s()) inside a loop: one query per iteration. '
                . 'Load one collection for all items before the loop and look items up in it.',
                self::ID,
                $class,
                $node->name->toString(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
