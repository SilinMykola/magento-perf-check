<?php

declare(strict_types=1);

namespace ComposerPluginFixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// A Composer plugin's command: Composer loads it with its own Symfony Console.
final class DeployCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}
