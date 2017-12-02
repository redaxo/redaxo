<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 */
class rex_command_cache_clear extends rex_console_command
{
    protected function configure()
    {
        $this->setName('cache:clear')
            ->setDescription(rex_i18n::msg('delete_cache'))
            ->setHelp(rex_i18n::msg('delete_cache_description'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $success = rex_delete_cache();
        $output->writeln($success);
    }
}
