<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\CallArgs;
use Silin\MagentoPerfCheck\PhpStan\LoopContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\LoopExitVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use Silin\MagentoPerfCheck\PhpStan\QueryCall;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeFinder;
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

    /** Currency::load() only sets the currency code. */
    public const LOAD_WITHOUT_QUERY = ['Magento\Directory\Model\Currency'];

    /** These read cached configuration (the "scopes" section), not the database. */
    public const REPOSITORIES_WITHOUT_QUERY = [
        'Magento\Store\Api\StoreRepositoryInterface',
        'Magento\Store\Api\WebsiteRepositoryInterface',
        'Magento\Store\Api\GroupRepositoryInterface',
    ];

    /**
     * Repositories whose get() and getById() keep what they loaded, by their arguments, for the rest of the request
     * (see the rule page): with the same arguments in every iteration, only the first one queries.
     */
    private const CACHING_REPOSITORIES = [
        'Magento\Catalog\Api\ProductRepositoryInterface',
        'Magento\Catalog\Api\CategoryRepositoryInterface',
        'Magento\Customer\Api\CustomerRepositoryInterface',
        'Magento\Sales\Api\OrderRepositoryInterface',
        'Magento\Quote\Api\CartRepositoryInterface',
        'Magento\Eav\Api\AttributeRepositoryInterface',
        'Magento\Catalog\Api\ProductAttributeRepositoryInterface',
    ];

    private readonly QueryCall $queries;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->queries = new QueryCall($reflectionProvider);
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
        ) {
            return [];
        }
        $class = $this->queries->modelOrRepositoryGet($node, $scope);
        if ($class === null || $this->repeatsCachedGet($node, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s::%s() is called inside a loop: one database query per iteration. '
                . 'Fetch all items before the loop with a collection or getList().',
                self::ID,
                $class,
                $node->name->toString(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    private function repeatsCachedGet(MethodCall $node, Scope $scope): bool
    {
        $writes = $node->getAttribute(LoopContextVisitor::LOOP_WRITES);
        if (!is_array($writes) || !$node->name instanceof Node\Identifier || $node->name->toLowerString() === 'load') {
            return false;
        }
        $type = $scope->getType($node->var);
        $caching = false;
        foreach (self::CACHING_REPOSITORIES as $repository) {
            $caching = $caching || MagentoTypes::isObjectOf($type, $repository);
        }
        if (!$caching || $node->isFirstClassCallable() || isset($writes['*'])) {
            return false;
        }
        // ProductRepository's fourth argument, $forceReload, loads again when it is true.
        $forceReload = CallArgs::of($node)[3]->value ?? null;
        if ($forceReload !== null && !($forceReload instanceof Node\Expr\ConstFetch && $forceReload->name->toLowerString() === 'false')) {
            return false;
        }
        foreach (CallArgs::of($node) as $arg) {
            // A named argument may be forceReload: true.
            if ($arg->unpack || $arg->name !== null || !self::sameInEveryIteration($arg->value, $writes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether an argument is built only from literals, constants and variables that no enclosing loop changes.
     *
     * @param array<mixed> $writes variable names the enclosing loops may change
     */
    private static function sameInEveryIteration(Node\Expr $expr, array $writes): bool
    {
        return match (true) {
            // A string with "{$var}" in it is a scalar node too.
            $expr instanceof Node\Scalar => (new NodeFinder())->findInstanceOf([$expr], Node\Expr\Variable::class) === [],
            $expr instanceof Node\Expr\ConstFetch, $expr instanceof Node\Expr\ClassConstFetch => true,
            $expr instanceof Node\Expr\Variable => is_string($expr->name) && $expr->name !== 'this' && !isset($writes[$expr->name]),
            default => false,
        };
    }
}
