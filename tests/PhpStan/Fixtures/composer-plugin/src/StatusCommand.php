<?php

declare(strict_types=1);

namespace ComposerPluginFixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// A Composer plugin's command that returns nothing: bin/magento never runs it.
final class StatusCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('ok');
    }
}
