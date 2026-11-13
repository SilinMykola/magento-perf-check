<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\PhpStan\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\MethodReturnStatementsNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\IntegerType;
use PHPStan\Type\NeverType;
use PHPStan\Type\VerbosityLevel;
use Silin\MagentoPerfCheck\PhpStan\ConditionalContextVisitor;
use Silin\MagentoPerfCheck\PhpStan\OutsideObjectManager;

/**
 * U-CLI-02: a console command whose execute() can return something other than an int. Symfony
 * Console 5.4, which Magento requires from 2.4.4-p12, 2.4.5-p11 and 2.4.6, throws a TypeError
 * then, where 4.4 only logged a deprecation. A value that may be an int is not reported, and neither
 * is a method with a native int or never return type: PHP fails its non-int result on every version. A named class in an if branch and Composer plugins
 * are left out, as U-CLI-01 leaves them out.
 *
 * @implements Rule<MethodReturnStatementsNode>
 */
final class ConsoleCommandExecuteResultRule implements Rule
{
    public const ID = 'U-CLI-02';
    public const IDENTIFIER = 'magentoPerf.consoleCommandExecuteResult';

    private const COMMAND = 'Symfony\Component\Console\Command\Command';

    private readonly OutsideObjectManager $outside;

    public function __construct()
    {
        $this->outside = new OutsideObjectManager();
    }

    public function getNodeType(): string
    {
        return MethodReturnStatementsNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->getClassReflection();
        if (strtolower($node->getMethodName()) !== 'execute'
            || !$class->isSubclassOf(self::COMMAND)
            // A method carries the mark only when its class is declared in an if branch.
            || (!$class->isAnonymous() && $node->getAttribute(ConditionalContextVisitor::IN_CONDITIONAL) === true)
            || $this->outside->contains($scope->getFile())
            // With a native int or never return type PHP itself fails a non-int result, on every version.
            || $this->hasIntReturnType($node)
        ) {
            return [];
        }

        [$problem, $line] = $this->firstNonIntResult($node);
        if ($problem === null) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '[%s] %s can %s. Symfony Console 5.4, which Magento requires from 2.4.4-p12, 2.4.5-p11 and 2.4.6, throws a TypeError '
                . 'when execute() returns anything but an int, so the command fails whenever it takes that path. Return an int, such as '
                . '\\Magento\\Framework\\Console\\Cli::RETURN_SUCCESS, on every path, and declare execute(...): int.',
                self::ID,
                $class->isAnonymous() ? 'execute() of an anonymous class' : $class->getName() . '::execute()',
                $problem,
            ))->identifier(self::IDENTIFIER)->line($line)->build(),
        ];
    }

    private function hasIntReturnType(MethodReturnStatementsNode $node): bool
    {
        if (!$node->hasNativeReturnTypehint()) {
            return false;
        }
        foreach ($node->getMethodReflection()->getVariants() as $variant) {
            $type = $variant->getNativeReturnType();
            if (!$type->isInteger()->yes() && !(new NeverType())->isSuperTypeOf($type)->yes()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{?string, int} what execute() can return that is not an int, and the line to report it at
     */
    private function firstNonIntResult(MethodReturnStatementsNode $node): array
    {
        if ($node->getYieldStatements() !== []) {
            return ['return a Generator', $node->getStartLine()];
        }
        foreach ($node->getReturnStatements() as $statement) {
            $return = $statement->getReturnNode();
            if ($return->expr === null) {
                return ['return without a value', $return->getStartLine()];
            }
            $type = $statement->getScope()->getType($return->expr);
            // Only a value that is never an int: one that may be an int is not reported.
            if ((new IntegerType())->isSuperTypeOf($type)->no()) {
                return [sprintf('return %s', $type->describe(VerbosityLevel::typeOnly())), $return->getStartLine()];
            }
        }
        if (!$node->getStatementResult()->isAlwaysTerminating()) {
            return ['end without returning a value', $node->getStartLine()];
        }

        return [null, $node->getStartLine()];
    }
}
