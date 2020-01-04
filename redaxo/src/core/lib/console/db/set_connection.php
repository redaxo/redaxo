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
class rex_command_db_set_connection extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription("Sets database connection credentials.\n  Checks by default if a database connection can be established with the new settings.")
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'database host')
            ->addOption('login', null, InputOption::VALUE_OPTIONAL, 'database user')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'database password')
            ->addOption('database', null, InputOption::VALUE_OPTIONAL, 'database name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Saves the settings, regardless of whether a database connection can be established.');
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

        if (!$changed) {
            throw new InvalidArgumentException('No database settings given.');
        }

        $settingsValid = rex_sql::checkDbConnection(
            $config['db'][1]['host'],
            $config['db'][1]['login'],
            $config['db'][1]['password'],
            $config['db'][1]['name'],
            false
        );

        if (true !== $settingsValid) {
            $io->error("Can't connect to database with the following error:\n" . $settingsValid);

            if (!$input->getOption('force')) {
                return 1;
            }
        } else {
            $io->success('Database test connection connection could be established.');
        }

        if (rex_file::putConfig($configFile, $config)) {
            $io->success('Database settings successfully saved');
            return 0;
        }
        $io->error('Database settings could\'t be saved.');
        return 1;
    }
}
