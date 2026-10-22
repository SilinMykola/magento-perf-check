<?php

declare(strict_types=1);

namespace MagentoPerfRules\PhpStan\Rule;

use MagentoPerfRules\PhpStan\MagentoTypes;
use MagentoPerfRules\PhpStan\ProjectIndexProvider;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-FPC-10: a full reindex started from an observer, controller or plugin, i.e. during
 * a request.
 *
 * @implements Rule<MethodCall>
 */
final class FullReindexRule implements Rule
{
    public const ID = 'P-FPC-10';
    public const IDENTIFIER = 'magentoPerf.fullReindex';

    private const INDEXER = 'Magento\Framework\Indexer\IndexerInterface';
    private const OBSERVER = 'Magento\Framework\Event\ObserverInterface';
    private const ACTION = 'Magento\Framework\App\ActionInterface';

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
        if (!$node->name instanceof Node\Identifier
            || $node->name->toLowerString() !== 'reindexall'
            || $this->types->subtypeOf($scope->getType($node->var), self::INDEXER) === null
        ) {
            return [];
        }
        $class = $scope->getClassReflection();
        $kind = match (true) {
            MagentoTypes::implements($class, self::OBSERVER) => 'observer',
            MagentoTypes::implements($class, self::ACTION) => 'controller',
            $class !== null && $this->index->get()->isPluginClass($class->getName()) => 'plugin',
            default => null,
        };
        if ($kind === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] reindexAll() in %s %s rebuilds the whole index during a request. '
                . 'Reindex the changed IDs with reindexList()/reindexRow(), or leave it to the indexer cron.',
                self::ID,
                $kind,
                $class?->getName() ?? '',
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
