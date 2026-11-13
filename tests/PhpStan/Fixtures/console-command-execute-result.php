<?php

declare(strict_types=1);

namespace ConsoleCommandExecuteResultFixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class NoReturn extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('done');
    }
}

final class ReturnsTrue extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('quiet')) {
            return 0;
        }

        return true;
    }
}

final class EmptyReturn extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('quiet')) {
            return;
        }

        return 0;
    }
}

final class VoidReturnType extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('done');
    }
}

final class SometimesFallsThrough extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('quiet')) {
            return Command::SUCCESS;
        }
        $output->writeln('done');
    }
}

final class Yields extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        yield 1;
    }
}

final class ReturnsInts extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('quiet')) {
            return 1;
        }

        return Command::SUCCESS;
    }
}

final class ReturnsMaybeInt extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->run2();
    }

    private function run2()
    {
        return 0;
    }
}

final class ThrowsOtherwise extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('quiet')) {
            return 0;
        }

        throw new \RuntimeException('failed');
    }
}

final class IntReturnType extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}

// PHP itself fails these on every version, whatever Symfony Console does: not an upgrade break.
final class IntReturnTypeWithoutReturn extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('done');
    }
}

final class NeverReturnTypeThatEnds extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): never
    {
        $output->writeln('done');
    }
}

final class NotACommand
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
    }
}

final class OtherMethod extends Command
{
    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}

if (class_exists(\stdClass::class)) {
    final class UnderVersionCheck extends Command
    {
        protected function execute(InputInterface $input, OutputInterface $output)
        {
        }
    }

    $command = new class () extends Command {
        protected function execute(InputInterface $input, OutputInterface $output)
        {
        }
    };
}

abstract class AbstractCommand extends Command
{
    abstract protected function execute(InputInterface $input, OutputInterface $output);
}
