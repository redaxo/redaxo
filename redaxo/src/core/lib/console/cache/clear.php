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
        $success = rex_delete_cache();
        $io = $this->getStyle($input, $output);
        
        if ($success) {
            $io->success('cache successfully cleared');
            return 0;
        }
        
        $io->error('clearing cache failed');
        return 1;
    }
}
