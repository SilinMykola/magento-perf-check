<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\TypeCombinator;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;

/**
 * P-DI-04: Magento creates every command of CommandList on every bin/magento run, whichever
 * command was asked for, and each command's dependencies with it.
 *
 * @implements Rule<InClassNode>
 */
final class EagerCommandDependencyRule implements Rule
{
    public const ID = 'P-DI-04';
    public const IDENTIFIER = 'magentoPerf.commandWithoutProxy';

    /**
     * Commands of core take these without a Proxy, so every run creates them anyway, and
     * instances are shared: another command that takes them adds nothing. In CommandList only the
     * sample data commands take Filesystem, but its constructor just stores three cheap arguments.
     */
    private const CREATED_ANYWAY = [
        'Magento\Framework\App\Cache\Manager',
        'Magento\Framework\App\State',
        'Magento\Framework\App\Config\ScopeConfigInterface',
        'Magento\Framework\Filesystem',
        'Magento\Framework\ObjectManagerInterface',
        'Magento\Store\Model\StoreManagerInterface',
        'Psr\Log\LoggerInterface',
    ];

    public function __construct(private readonly ProjectIndexProvider $index)
    {
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();
        $index = $this->index->get();
        if (!$index->isConsoleCommand($class->getName()) || !$class->hasConstructor()) {
            return [];
        }

        $classes = [$class->getName(), ...array_map(static fn ($parent): string => $parent->getName(), $class->getParents())];
        $eager = [];
        // From reflection, because a command without a constructor of its own gets its parent's dependencies.
        foreach ($class->getConstructor()->getOnlyVariant()->getParameters() as $param) {
            // The object manager passes the default of an optional parameter, and creates nothing.
            if ($param->isOptional()) {
                continue;
            }
            $typeName = TypeCombinator::removeNull($param->getNativeType())->getObjectClassNames()[0] ?? null;
            if ($typeName === null || in_array($typeName, self::CREATED_ANYWAY, true) || preg_match('/(\\\\Proxy|Factory)$/', $typeName) === 1) {
                continue;
            }
            foreach ($classes as $configured) {
                if ($index->hasProxyArgument($configured, $param->getName())) {
                    continue 2;
                }
            }
            $eager[] = '$' . $param->getName();
        }
        if ($eager === []) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s is created on every bin/magento run, whichever command is asked for, and so are its dependencies %s. '
                . 'Configure a Proxy in di.xml for those that are costly to create.',
                self::ID,
                $class->getName(),
                implode(', ', $eager),
            ))->identifier(self::IDENTIFIER)->line($node->getOriginalNode()->getMethod('__construct')?->getStartLine() ?? $node->getStartLine())->build(),
        ];
    }
}
