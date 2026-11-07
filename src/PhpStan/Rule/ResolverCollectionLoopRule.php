<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;

/**
 * X-GQL-02, for a database collection that a foreach loads.
 *
 * @implements Rule<Foreach_>
 */
final class ResolverCollectionLoopRule implements Rule
{
    private readonly MagentoTypes $types;

    public function __construct(ReflectionProvider $reflectionProvider, private readonly ProjectIndexProvider $index)
    {
        $this->types = new MagentoTypes($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return Foreach_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $field = ResolverQueryRule::listField($node, $scope, $this->index);
        if ($field === null || $this->isReportedCall($node->expr)) {
            return [];
        }
        $class = $this->types->subtypeOf($scope->getType($node->expr), MagentoTypes::DB_COLLECTION);

        return $class === null ? [] : [ResolverQueryRule::error('foreach over ' . $class, $field)];
    }

    /** foreach ($collection->load() as ...): ResolverQueryRule reports this one query already. */
    private function isReportedCall(Node\Expr $iterated): bool
    {
        return $iterated instanceof Node\Expr\MethodCall
            && $iterated->name instanceof Node\Identifier
            && in_array($iterated->name->toLowerString(), ResolverQueryRule::COLLECTION_QUERIES, true);
    }
}
