<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\LoopContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;

/**
 * X-GQL-02: a database query in a ResolverInterface resolver of a field on a type returned
 * inside a list runs once per list element. Queries in a closure are deferred, usually
 * so that one query serves all elements, and are left out, unless the closure is itself
 * a loop body. A constructor runs once per request and is left out too.
 *
 * @implements Rule<MethodCall>
 */
final class ResolverQueryRule implements Rule
{
    public const ID = 'X-GQL-02';
    public const IDENTIFIER = 'magentoPerf.resolverQuery';

    private const RESOLVER = 'Magento\Framework\GraphQl\Query\ResolverInterface';
    private const DB_ADAPTER = 'Magento\Framework\DB\Adapter\AdapterInterface';

    /** Calls that run the query of a collection. */
    public const COLLECTION_QUERIES = ['load', 'getitems', 'getsize', 'getfirstitem', 'getlastitem', 'getdata', 'count', 'toarray', 'getcolumnvalues', 'getallids'];
    private const ADAPTER_QUERIES = ['query', 'fetchall', 'fetchrow', 'fetchone', 'fetchcol', 'fetchpairs', 'fetchassoc'];

    private readonly MagentoTypes $types;

    public function __construct(ReflectionProvider $reflectionProvider, private readonly ProjectIndexProvider $index)
    {
        $this->types = new MagentoTypes($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier) {
            return [];
        }
        $field = self::listField($node, $scope, $this->index);
        if ($field === null) {
            return [];
        }
        $class = $this->queryClass($node->name->toLowerString(), $scope->getType($node->var), $scope);
        if ($class === null) {
            return [];
        }

        return [self::error($class . '::' . $node->name->toString() . '()', $field)];
    }

    /**
     * The list field whose resolver runs $node once per list element, or null when it runs
     * once per request, as a batch resolver, a deferred closure or a constructor does.
     */
    public static function listField(Node $node, Scope $scope, ProjectIndexProvider $index): ?string
    {
        $class = $scope->getClassReflection();
        if (!MagentoTypes::implements($class, self::RESOLVER)
            || ($scope->isInAnonymousFunction() && $node->getAttribute(LoopContextVisitor::IN_LOOP) !== true)
            || strtolower((string) $scope->getFunctionName()) === '__construct'
        ) {
            return null;
        }

        return $index->get()->listFieldResolvedBy((string) $class?->getName());
    }

    public static function error(string $query, string $field): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(
            '[%s] %s runs a query in the resolver of %s, a field on a type returned inside a list: one query per list element. '
            . 'Implement BatchResolverInterface, or collect the ids and load them once in a deferred value.',
            self::ID,
            $query,
            $field,
        ))->identifier(self::IDENTIFIER)->build();
    }

    private function queryClass(string $method, Type $type, Scope $scope): ?string
    {
        if (in_array($method, self::COLLECTION_QUERIES, true)) {
            $collection = $this->types->subtypeOf($type, MagentoTypes::DB_COLLECTION);
            if ($collection !== null) {
                return $collection;
            }
        }
        if (in_array($method, self::ADAPTER_QUERIES, true)) {
            return $this->types->subtypeOf($type, self::DB_ADAPTER);
        }
        if ($method === 'load') {
            foreach (ModelLoadInLoopRule::LOAD_WITHOUT_QUERY as $noQuery) {
                if (MagentoTypes::isObjectOf($type, $noQuery)) {
                    return null;
                }
            }

            return $this->types->subtypeOf($type, MagentoTypes::ABSTRACT_MODEL) ?? $this->types->resourceModel($type);
        }
        if (!in_array($method, ['get', 'getbyid', 'getlist'], true)) {
            return null;
        }
        foreach (ModelLoadInLoopRule::REPOSITORIES_WITHOUT_QUERY as $noQuery) {
            if (MagentoTypes::isObjectOf($type, $noQuery)) {
                return null;
            }
        }
        if ($method === 'getlist' && !MagentoTypes::takesSearchCriteria($type, $scope)) {
            return null;
        }

        return $this->types->repository($type);
    }
}
