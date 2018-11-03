<?php

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_command_db_set_connection_options extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('Sets database connection credentials')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'database host')
            ->addOption('login', null, InputOption::VALUE_OPTIONAL, 'database user')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'database password')
            ->addOption('database', null, InputOption::VALUE_OPTIONAL, 'database name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $configFile = rex_path::coreData('config.yml');
        $config = rex_file::getConfig($configFile);

        $changed = false;
        if ($input->hasOption('host')) {
            $config['db'][1]['host'] = $input->getOption('host');
            $changed = true;
        }
        if ($input->hasOption('login')) {
            $config['db'][1]['login'] = $input->getOption('login');
            $changed = true;
        }
        if ($input->hasOption('password')) {
            $config['db'][1]['password'] = $input->getOption('password');
            $changed = true;
        }
        if ($input->hasOption('database')) {
            $config['db'][1]['name'] = $input->getOption('database');
            $changed = true;
        }

        if ($changed) {
            throw new InvalidArgumentException('No database settings given.');
        }

        if (rex_file::putConfig($configFile, $config)) {
            $io->success('Database settings successfully saved');
            return 0;
        }
        $io->error('Database settings could\'t be saved.');
        return 1;
    }
}
