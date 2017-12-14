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
        $this
            ->setDescription('Clears the redaxo core cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $successMsg = rex_delete_cache();
        $io = $this->getStyle($input, $output);
        
        $io->success($successMsg);
        return 0;
    }
}
