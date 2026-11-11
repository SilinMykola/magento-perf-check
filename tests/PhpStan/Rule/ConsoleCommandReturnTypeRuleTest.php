<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Silin\MagentoPerfCheck\PhpStan\Rule\ConsoleCommandReturnTypeRule;

/**
 * @extends RuleTestCase<ConsoleCommandReturnTypeRule>
 */
final class ConsoleCommandReturnTypeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ConsoleCommandReturnTypeRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testCommandsWithoutTheSymfony74ReturnType(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/console-command-return-type.php', __DIR__ . '/../Fixtures/composer-plugin/src/DeployCommand.php'], [
            [self::message('ConsoleCommandReturnTypeFixture\\NoReturnType::execute()'), 13],
            // Through an abstract parent, and a return type other than int.
            [self::message('ConsoleCommandReturnTypeFixture\\VoidReturnType::execute()'), 25],
            // ?int is wider than int.
            [self::message('ConsoleCommandReturnTypeFixture\\NullableIntReturnType::execute()'), 32],
            // execute() from a trait, reported at the class.
            [self::message('ConsoleCommandReturnTypeFixture\\FromTrait::execute()'), 96],
            // An anonymous class in an if branch is not a variant behind a version check.
            [self::message('execute() of an anonymous class'), 113],
            [self::message('ConsoleCommandReturnTypeFixture\\BadBase::execute()'), 126],
            // ?int from a trait.
            [self::message('ConsoleCommandReturnTypeFixture\\FromTraitWithNullableInt::execute()'), 145],
        ]);
    }

    private static function message(string $subject): string
    {
        return sprintf(
            '[U-CLI-01] %s has no int return type. Magento 2.4.9 requires Symfony Console 7.4, whose Command::execute() returns int, '
            . 'so PHP cannot load this class; registered as a command in di.xml, it makes every bin/magento call fail. Declare execute(...): int.',
            $subject,
        );
    }
}
