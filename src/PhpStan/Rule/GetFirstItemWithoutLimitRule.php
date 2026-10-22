<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use MagentoPerfRules\PhpStan\CollectionStateVisitor;
use MagentoPerfRules\Report\NoticePrefix;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use MagentoPerfRules\PhpStan\MagentoTypes;

/**
 * P-COL-02: getFirstItem() loads the whole collection; without setPageSize()
 * there is no LIMIT in the query.
 *
 * @implements Rule<MethodCall>
 */
final class GetFirstItemWithoutLimitRule implements Rule
{
    public const ID = 'P-COL-02';
    public const IDENTIFIER = 'magentoPerf.getFirstItemWithoutLimit';

    /** Prefix of the notice reported for receivers whose type is unknown, when enabled. */
    public const UNTYPED_NOTICE_PREFIX = NoticePrefix::UNTYPED_RECEIVER;

    private const DB_COLLECTION = 'Magento\Framework\Data\Collection\AbstractDb';

    public function __construct(
        private readonly ReflectionProvider $reflectionProvider,
        private readonly bool $reportUntypedReceivers = false,
    ) {
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier
            || $node->name->toLowerString() !== 'getfirstitem'
            || $node->getAttribute(CollectionStateVisitor::LIMITED) === true
            || $node->getAttribute(CollectionStateVisitor::LOADED) === true
            || $node->getAttribute(CollectionStateVisitor::UNIQUE_FILTER) === true
        ) {
            return [];
        }
        $type = $scope->getType($node->var);

        if (!MagentoTypes::isObjectOf($type, self::DB_COLLECTION)) {
            if ($this->reportUntypedReceivers && $this->isUntyped($type->getObjectClassNames())) {
                return [
                    RuleErrorBuilder::message(self::UNTYPED_NOTICE_PREFIX . ' getFirstItem() on a receiver of unknown type')
                        ->identifier(self::IDENTIFIER . 'Untyped')
                        ->build(),
                ];
            }

            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s::getFirstItem() without setPageSize(): the query has no LIMIT and loads every matching row. '
                . 'Call setPageSize(1) before getFirstItem().',
                self::ID,
                $type->getObjectClassNames()[0] ?? self::DB_COLLECTION,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    /**
     * @param list<string> $classNames
     */
    private function isUntyped(array $classNames): bool
    {
        if ($classNames === []) {
            return true;
        }
        foreach ($classNames as $className) {
            if (!$this->reflectionProvider->hasClass($className)) {
                return true;
            }
        }

        return false;
    }
}
