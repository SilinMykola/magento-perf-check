<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\LoopContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-LOOP-01: AbstractModel::load() or a repository get()/getById() inside a loop
 * runs one query per iteration.
 *
 * @implements Rule<MethodCall>
 */
final class ModelLoadInLoopRule implements Rule
{
    public const ID = 'P-LOOP-01';
    public const IDENTIFIER = 'magentoPerf.loopModelLoad';

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
        $method = $node->name->toString();
        $type = $scope->getType($node->var);

        $class = match (strtolower($method)) {
            'load' => $this->types->subtypeOf($type, MagentoTypes::ABSTRACT_MODEL),
            'get', 'getbyid' => $this->types->repository($type),
            default => null,
        };
        if ($class === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s::%s() is called inside a loop: one database query per iteration. '
                . 'Fetch all items before the loop with a collection or getList().',
                self::ID,
                $class,
                $method,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
