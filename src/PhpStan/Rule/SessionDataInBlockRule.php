<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-FPC-02: a block or view model reads customer data from the session; on cacheable
 * pages that data is not there (or ends up in the cached page). A class that frontend layouts place only on
 * pages that are never cached, such as the cart, the checkout or the customer account, is left out, unless a
 * subclass of it is placed on a page that may be cached and runs the same code there.
 *
 * @implements Rule<MethodCall>
 */
final class SessionDataInBlockRule implements Rule
{
    public const ID = 'P-FPC-02';
    public const IDENTIFIER = 'magentoPerf.sessionDataInBlock';

    /**
     * getCustomerGroupId() is left out: depersonalization keeps the customer group, and the
     * group is part of the page cache key.
     */
    private const METHODS = ['getcustomer', 'getcustomerid', 'getquote'];
    private const SESSION = 'Magento\Framework\Session\SessionManagerInterface';
    private const ABSTRACT_BLOCK = 'Magento\Framework\View\Element\AbstractBlock';
    private const VIEW_MODEL = 'Magento\Framework\View\Element\Block\ArgumentInterface';
    private const COLLECTION = 'Magento\Framework\Data\Collection';

    private readonly MagentoTypes $types;

    /** @var ?list<string> classes placed on pages that may be cached */
    private ?array $onCacheablePages = null;

    /** @var array<string, bool> class => placed only on uncacheable pages */
    private array $onlyOnUncacheable = [];

    public function __construct(private readonly ReflectionProvider $reflectionProvider, private readonly ProjectIndexProvider $index)
    {
        $this->types = new MagentoTypes($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier || !in_array($node->name->toLowerString(), self::METHODS, true)) {
            return [];
        }
        $class = $scope->getClassReflection();
        // Collections implement ArgumentInterface (through CollectionDataSourceInterface) but are not view models.
        $viewModel = MagentoTypes::implements($class, self::VIEW_MODEL) && !MagentoTypes::extends($class, self::COLLECTION);
        $frontendBlock = MagentoTypes::extends($class, self::ABSTRACT_BLOCK)
            && !str_contains((string) $class?->getName(), '\\Block\\Adminhtml\\');
        if (!$frontendBlock && !$viewModel) {
            return [];
        }
        $session = $this->types->subtypeOf($scope->getType($node->var), self::SESSION);
        if ($session === null || ($class !== null && $this->onlyOnUncacheablePages($class))) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s reads %s::%s() during rendering. On cacheable pages the session is depersonalized, '
                . 'so this data is missing or cached for everyone; load it through customer data sections.',
                self::ID,
                $class?->getName() ?? 'This class',
                $session,
                $node->name->toString(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    private function onlyOnUncacheablePages(ClassReflection $class): bool
    {
        return $this->onlyOnUncacheable[$class->getName()] ??= $this->decideOnlyOnUncacheable($class);
    }

    private function decideOnlyOnUncacheable(ClassReflection $class): bool
    {
        $index = $this->index->get();
        if (!$index->isOnlyOnUncacheablePages($class->getName())) {
            return false;
        }
        // A subclass on a cacheable page runs this code there; it may be outside the analyzed files.
        foreach ($this->onCacheablePages ??= $index->classesOnCacheablePages() as $placed) {
            if ($this->reflectionProvider->hasClass($placed) && $this->reflectionProvider->getClass($placed)->isSubclassOf($class->getName())) {
                return false;
            }
        }

        return true;
    }
}
