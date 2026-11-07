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
use PHPStan\Type\Type;

/**
 * P-LOOP-09: a repository getList(SearchCriteriaInterface) once per item of a foreach or
 * array_map(). A while, do or for loop around getList() is usually paging through one
 * result, so it is left out. A getList() whose first parameter is something else, such as
 * one SKU or an entity type, cannot take the values of all items at once.
 *
 * @implements Rule<MethodCall>
 */
final class RepositoryListInLoopRule implements Rule
{
    public const ID = 'P-LOOP-09';
    public const IDENTIFIER = 'magentoPerf.loopRepositoryList';

    private const SEARCH_CRITERIA = 'Magento\Framework\Api\SearchCriteriaInterface';

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
        if ($node->getAttribute(LoopContextVisitor::IN_ITEM_LOOP) !== true
            || !$node->name instanceof Node\Identifier
            || $node->name->toLowerString() !== 'getlist'
        ) {
            return [];
        }
        $type = $scope->getType($node->var);
        $class = $this->types->repository($type);
        if ($class === null || !$this->takesSearchCriteria($type, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s::getList() is called once per item of a loop: at least one query per iteration. '
                . 'Collect the values first and make one getList() with an "in" filter.',
                self::ID,
                $class,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    private function takesSearchCriteria(Type $type, Scope $scope): bool
    {
        $method = $scope->getMethodReflection($type, 'getList');
        foreach ($method?->getVariants() ?? [] as $variant) {
            $first = $variant->getParameters()[0] ?? null;
            if ($first !== null && MagentoTypes::isObjectOf($first->getType(), self::SEARCH_CRITERIA)) {
                return true;
            }
        }

        return false;
    }
}
