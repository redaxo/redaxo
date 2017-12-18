<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 */
class rex_command_cronjob_run extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('Executes cronjobs of the "script" environment');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        // indicator constant, kept for BC
        define('REX_CRONJOB_SCRIPT', true);

        $nexttime = rex_package::get('cronjob')->getConfig('nexttime', 0);

        if ($nexttime != 0 && time() >= $nexttime) {
            rex_cronjob_manager_sql::factory()->check();

            $io->success('cronjobs checked');
            return 0;
        }

        $io->success('cronjobs skipped');
        return 0;
    }
}
