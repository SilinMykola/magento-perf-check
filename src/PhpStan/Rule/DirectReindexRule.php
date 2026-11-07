<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\IndexerModeVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * P-IDX-01: IndexerInterface::reindexRow() or reindexList() in an observer, controller or
 * plugin that never asks isScheduled(). These methods do not check the indexer mode, so the
 * request reindexes synchronously even in Update on Schedule mode.
 *
 * @implements Rule<MethodCall>
 */
final class DirectReindexRule implements Rule
{
    public const ID = 'P-IDX-01';
    public const IDENTIFIER = 'magentoPerf.directReindex';

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
            || !in_array($node->name->toLowerString(), ['reindexrow', 'reindexlist'], true)
            || $node->getAttribute(IndexerModeVisitor::MODE_CHECKED) === true
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
                '[%s] %s() in %s %s reindexes during the request, even in Update on Schedule mode. '
                . 'Check $indexer->isScheduled() first, or use the indexer\'s processor.',
                self::ID,
                $node->name->toString(),
                $kind,
                $class?->getName() ?? '',
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
