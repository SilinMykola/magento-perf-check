<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Silin\MagentoPerfCheck\PhpStan\Rule\ConsoleCommandExecuteResultRule;

/**
 * @extends RuleTestCase<ConsoleCommandExecuteResultRule>
 */
final class ConsoleCommandExecuteResultRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ConsoleCommandExecuteResultRule();
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon'];
    }

    public function testCommandsThatCanReturnANonInt(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/console-command-execute-result.php', __DIR__ . '/../Fixtures/composer-plugin/src/StatusCommand.php'], [
            [self::message('ConsoleCommandExecuteResultFixture\\NoReturn::execute()', 'end without returning a value'), 13],
            [self::message('ConsoleCommandExecuteResultFixture\\ReturnsTrue::execute()', 'return true'), 27],
            [self::message('ConsoleCommandExecuteResultFixture\\EmptyReturn::execute()', 'return without a value'), 36],
            // A void return type ends without a value too; U-CLI-01 also reports it for 2.4.9.
            [self::message('ConsoleCommandExecuteResultFixture\\VoidReturnType::execute()', 'end without returning a value'), 45],
            [self::message('ConsoleCommandExecuteResultFixture\\SometimesFallsThrough::execute()', 'end without returning a value'), 53],
            [self::message('ConsoleCommandExecuteResultFixture\\Yields::execute()', 'return a Generator'), 64],
            // An anonymous class in an if branch is not a variant behind a version check.
            [self::message('execute() of an anonymous class', 'end without returning a value'), 160],
        ]);
    }

    private static function message(string $subject, string $problem): string
    {
        return sprintf(
            '[U-CLI-02] %s can %s. Symfony Console 5.4, which Magento requires from 2.4.4-p12, 2.4.5-p11 and 2.4.6, throws a TypeError '
            . 'when execute() returns anything but an int, so the command fails whenever it takes that path. Return an int, such as '
            . '\\Magento\\Framework\\Console\\Cli::RETURN_SUCCESS, on every path, and declare execute(...): int.',
            $subject,
            $problem,
        );
    }
}
