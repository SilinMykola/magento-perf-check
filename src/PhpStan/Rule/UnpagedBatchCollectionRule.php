<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Silin\MagentoPerfCheck\PhpStan\CollectionStateVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;

/**
 * P-COL-04: a cron job or console command that walks a collection without a page size holds
 * every matching row in memory at once.
 *
 * @implements Rule<Foreach_>
 */
final class UnpagedBatchCollectionRule implements Rule
{
    public const ID = 'P-COL-04';
    public const IDENTIFIER = 'magentoPerf.unpagedBatchCollection';

    private const CONSOLE_COMMAND = 'Symfony\Component\Console\Command\Command';

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
        if ($node->getAttribute(CollectionStateVisitor::CREATED_HERE) !== true
            || $node->getAttribute(CollectionStateVisitor::LIMITED) === true
            || $node->getAttribute(CollectionStateVisitor::UNIQUE_FILTER) === true
        ) {
            return [];
        }
        $class = $scope->getClassReflection();
        if ($class === null
            || (!$this->index->get()->isCronJob($class->getName())
                && !MagentoTypes::isObjectOf(new ObjectType($class->getName()), self::CONSOLE_COMMAND))
        ) {
            return [];
        }

        $iterated = $node->expr;
        if ($iterated instanceof MethodCall && $iterated->name instanceof Node\Identifier && $iterated->name->toLowerString() === 'getitems') {
            $iterated = $iterated->var;
        }
        $collection = $this->types->subtypeOf($scope->getType($iterated), MagentoTypes::DB_COLLECTION);
        if ($collection === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s is iterated without a page size in a batch job: all matching rows are held in memory at once. '
                . 'Walk it in pages with setPageSize() and setCurPage().',
                self::ID,
                $collection,
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }
}
