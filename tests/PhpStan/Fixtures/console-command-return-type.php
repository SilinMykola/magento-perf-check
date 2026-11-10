<?php

declare(strict_types=1);

namespace ConsoleCommandReturnTypeFixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class NoReturnType extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}

abstract class BaseCommand extends Command
{
}

final class VoidReturnType extends BaseCommand
{
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
    }
}

final class NullableIntReturnType extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        return null;
    }
}

final class IntReturnType extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}

final class NeverReturns extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): never
    {
        throw new \RuntimeException('stop');
    }
}

// No execute() of its own.
final class InheritsExecute extends IntReturnType
{
}

// Not a console command.
final class NotACommand
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}

// One variant behind a version check.
if (!interface_exists(\Symfony\Component\Console\Command\SignalableCommandInterface::class)) {
    final class GuardedVariant extends Command
    {
        public function execute(InputInterface $input, OutputInterface $output)
        {
            return 0;
        }
    }
}

trait ExecutesWithoutType
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}

trait ExecutesWithInt
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}

// execute() comes from a trait.
final class FromTrait extends Command
{
    use ExecutesWithoutType;
}

final class FromTraitWithInt extends Command
{
    use ExecutesWithInt;
}

final class Factory
{
    public function create(bool $legacy): Command
    {
        if ($legacy) {
            // An anonymous class in an if branch runs when that line does.
            return new class extends Command {
                public function execute(InputInterface $input, OutputInterface $output)
                {
                    return 0;
                }
            };
        }

        return new IntReturnType();
    }
}

class BadBase extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}

// Inherited: reported once, at the parent.
final class ChildOfBadBase extends BadBase
{
}

trait ExecutesWithNullableInt
{
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        return null;
    }
}

final class FromTraitWithNullableInt extends Command
{
    use ExecutesWithNullableInt;
}
