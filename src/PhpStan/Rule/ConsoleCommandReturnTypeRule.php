<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Silin\MagentoPerfCheck\PhpStan\ConditionalContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\OutsideObjectManager;

/**
 * U-CLI-01: a console command whose execute() has no int return type, declared in the class or a
 * trait it uses. Symfony Console 7.4, which Magento 2.4.9 requires, declares `execute(...): int`,
 * so such a class cannot be loaded. A named class in an if branch is left out, and so are Composer
 * plugins, which bin/magento does not load.
 *
 * @implements Rule<InClassNode>
 */
final class ConsoleCommandReturnTypeRule implements Rule
{
    public const ID = 'U-CLI-01';
    public const IDENTIFIER = 'magentoPerf.consoleCommandReturnType';

    private const COMMAND = 'Symfony\Component\Console\Command\Command';

    private readonly OutsideObjectManager $outside;

    public function __construct()
    {
        $this->outside = new OutsideObjectManager();
    }

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();
        $declaration = $node->getOriginalNode();
        if (!$class->isSubclassOf(self::COMMAND)
            // A named class in an if branch is usually one variant behind a version check.
            || (!$class->isAnonymous() && $declaration->getAttribute(ConditionalContextVisitor::IN_CONDITIONAL) === true)
            || $this->outside->contains($scope->getFile())
        ) {
            return [];
        }
        $own = $declaration->getMethod('execute');
        if ($own !== null) {
            $returnType = $own->getReturnType();
            $compatible = $returnType instanceof Identifier && in_array($returnType->toLowerString(), ['int', 'never'], true);
            $line = $own->getStartLine();
        } else {
            // From a trait: PHP checks it against the parent as if the class declared it.
            $native = $class->getNativeReflection();
            if (!$native->hasMethod('execute') || $native->getMethod('execute')->getDeclaringClass()->getName() !== $class->getName()) {
                return [];
            }
            $returnType = $native->getMethod('execute')->getReturnType();
            $compatible = $returnType instanceof \ReflectionNamedType && !$returnType->allowsNull()
                && in_array(strtolower($returnType->getName()), ['int', 'never'], true);
            $line = $declaration->getStartLine();
        }
        if ($compatible) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s has no int return type. Magento 2.4.9 requires Symfony Console 7.4, whose Command::execute() returns int, '
                . 'so PHP cannot load this class; registered as a command in di.xml, it makes every bin/magento call fail. Declare execute(...): int.',
                self::ID,
                $class->isAnonymous() ? 'execute() of an anonymous class' : $class->getName() . '::execute()',
            ))->identifier(self::IDENTIFIER)->line($line)->build(),
        ];
    }
}
