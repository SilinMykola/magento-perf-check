<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use MagentoPerfRules\PhpStan\CollectionStateVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use MagentoPerfRules\PhpStan\MagentoTypes;

/**
 * P-COL-01: count($collection) loads every row, while getSize() runs a COUNT query.
 *
 * @implements Rule<FuncCall>
 */
final class CollectionCountRule implements Rule
{
    public const ID = 'P-COL-01';
    public const IDENTIFIER = 'magentoPerf.collectionCount';

    private const DB_COLLECTION = 'Magento\Framework\Data\Collection\AbstractDb';

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Name
            || !in_array($node->name->toLowerString(), ['count', 'sizeof'], true)
            || $node->getAttribute(CollectionStateVisitor::LOADED) === true
        ) {
            return [];
        }
        $argument = $node->getArgs()[0] ?? null;
        if ($argument === null) {
            return [];
        }
        $type = $scope->getType($argument->value);
        if (!MagentoTypes::isObjectOf($type, self::DB_COLLECTION)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s() on %s loads every row of the collection to count it. Use getSize(), which runs a COUNT query.',
                self::ID,
                $node->name->toLowerString(),
                $type->getObjectClassNames()[0] ?? self::DB_COLLECTION,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
