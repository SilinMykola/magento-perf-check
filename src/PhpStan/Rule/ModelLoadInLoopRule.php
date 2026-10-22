<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use MagentoPerfRules\PhpStan\LoopContextVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

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

    private const ABSTRACT_MODEL = 'Magento\Framework\Model\AbstractModel';
    private const REPOSITORY_SUFFIX = 'RepositoryInterface';

    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
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
            'load' => (new ObjectType(self::ABSTRACT_MODEL))->isSuperTypeOf($type)->yes()
                ? $this->firstClassName($type->getObjectClassNames())
                : null,
            'get', 'getbyid' => $this->repositoryClass($type->getObjectClassNames()),
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

    /**
     * @param list<string> $classNames
     */
    private function firstClassName(array $classNames): ?string
    {
        return $classNames[0] ?? null;
    }

    /**
     * The first class that is, or implements, an interface named *RepositoryInterface.
     *
     * @param list<string> $classNames
     */
    private function repositoryClass(array $classNames): ?string
    {
        foreach ($classNames as $className) {
            if (!$this->reflectionProvider->hasClass($className)) {
                continue;
            }
            $reflection = $this->reflectionProvider->getClass($className);
            $names = array_keys($reflection->getInterfaces());
            if ($reflection->isInterface()) {
                $names[] = $reflection->getName();
            }
            foreach ($names as $name) {
                if (str_ends_with($name, self::REPOSITORY_SUFFIX)) {
                    return $reflection->getName();
                }
            }
        }

        return null;
    }
}
