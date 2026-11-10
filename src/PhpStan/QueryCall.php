<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\ModelLoadInLoopRule;

/**
 * The calls that run one query each, as P-LOOP-01, P-LOOP-09 and P-LOOP-10 define them, without
 * the loop context. P-LOOP-12 uses all three to find methods that query on every call.
 */
final class QueryCall
{
    private readonly MagentoTypes $types;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->types = new MagentoTypes($reflectionProvider);
    }

    /** P-LOOP-01: the class of a model load() or a repository get()/getById(). */
    public function modelOrRepositoryGet(MethodCall $call, Scope $scope): ?string
    {
        $method = self::method($call);
        if (!in_array($method, ['load', 'get', 'getbyid'], true)) {
            return null;
        }
        $type = $scope->getType($call->var);
        $excluded = $method === 'load' ? ModelLoadInLoopRule::LOAD_WITHOUT_QUERY : ModelLoadInLoopRule::REPOSITORIES_WITHOUT_QUERY;
        foreach ($excluded as $noQuery) {
            if (MagentoTypes::isObjectOf($type, $noQuery)) {
                return null;
            }
        }

        return $method === 'load'
            ? $this->types->subtypeOf($type, MagentoTypes::ABSTRACT_MODEL)
            : $this->types->repository($type);
    }

    /** P-LOOP-09: the class of a repository getList() that takes search criteria. */
    public function repositoryList(MethodCall $call, Scope $scope): ?string
    {
        if (self::method($call) !== 'getlist') {
            return null;
        }
        $type = $scope->getType($call->var);
        $class = $this->types->repository($type);

        return $class !== null && MagentoTypes::takesSearchCriteria($type, $scope) ? $class : null;
    }

    /** P-LOOP-10: the class of a resource model load(). */
    public function resourceLoad(MethodCall $call, Scope $scope): ?string
    {
        return self::method($call) === 'load' ? $this->types->resourceModel($scope->getType($call->var)) : null;
    }

    /** "Class::method()" when the call is any of the three, otherwise null. */
    public function describe(MethodCall $call, Scope $scope): ?string
    {
        $class = $this->modelOrRepositoryGet($call, $scope)
            ?? $this->repositoryList($call, $scope)
            ?? $this->resourceLoad($call, $scope);

        return $class !== null && $call->name instanceof Node\Identifier ? $class . '::' . $call->name->toString() . '()' : null;
    }

    private static function method(MethodCall $call): ?string
    {
        return $call->name instanceof Node\Identifier ? $call->name->toLowerString() : null;
    }
}
