<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use Silin\MagentoPerfCheck\PhpStan\ConditionalContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\MagentoTypes;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;

/**
 * X-SETUP-01: heavy work in Setup\Recurring or Setup\RecurringData, which run on every
 * setup:upgrade. Work behind an if is left out: it does not run every time.
 *
 * @implements Rule<MethodCall>
 */
final class RecurringSetupRule implements Rule
{
    public const ID = 'X-SETUP-01';
    public const IDENTIFIER = 'magentoPerf.heavyRecurringSetup';

    private const INDEXER = 'Magento\Framework\Indexer\IndexerInterface';

    private readonly MagentoTypes $types;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->types = new MagentoTypes($reflectionProvider);
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $scope->getClassReflection()?->getName() ?? '';
        if (!$node->name instanceof Node\Identifier
            || strtolower((string) $scope->getFunctionName()) !== 'install'
            || $node->getAttribute(ConditionalContextVisitor::IN_CONDITIONAL) === true
            || preg_match('/\\\\Setup\\\\(Recurring|RecurringData)$/', $class, $m) !== 1
            || !$this->isHeavy($node->name->toLowerString(), $scope->getType($node->var))
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s::install() does heavy work (%s()); it runs on every setup:upgrade. '
                . 'Move one-time work to a data or schema patch.',
                self::ID,
                $m[1],
                $node->name->toString(),
            ))->identifier(self::IDENTIFIER)->build(),
        ];
    }

    /**
     * Writes and reindexing only. Reading a few rows on every upgrade is cheap, and a recurring
     * script may need to read them to decide what to do.
     */
    private function isHeavy(string $method, Type $type): bool
    {
        return match ($method) {
            'save' => $this->types->subtypeOf($type, MagentoTypes::ABSTRACT_MODEL) !== null
                || $this->types->subtypeOf($type, MagentoTypes::RESOURCE_MODEL) !== null
                || $this->types->repository($type) !== null,
            'reindexall', 'reindexlist', 'reindexrow' => $this->types->subtypeOf($type, self::INDEXER) !== null,
            default => false,
        };
    }
}
