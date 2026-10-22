<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use MagentoPerfRules\PhpStan\LoopContextVisitor;
use MagentoPerfRules\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-LOOP-03: save() or delete() of a model, repository or resource model inside a loop.
 *
 * @implements Rule<MethodCall>
 */
final class SaveInLoopRule implements Rule
{
    public const ID = 'P-LOOP-03';
    public const IDENTIFIER = 'magentoPerf.loopSave';

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
        if ($node->getAttribute(LoopContextVisitor::IN_LOOP) !== true || !$node->name instanceof Node\Identifier) {
            return [];
        }
        $method = $node->name->toLowerString();
        $type = $scope->getType($node->var);

        $class = match ($method) {
            'save', 'delete' => $this->types->subtypeOf($type, MagentoTypes::ABSTRACT_MODEL)
                ?? $this->types->subtypeOf($type, MagentoTypes::RESOURCE_MODEL)
                ?? $this->types->repository($type),
            'deletebyid' => $this->types->repository($type),
            default => null,
        };
        if ($class === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s::%s() is called inside a loop: one write (and its events and plugins) per iteration. '
                . 'Prefer a batch operation where one exists.',
                self::ID,
                $class,
                $node->name->toString(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
