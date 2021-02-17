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
class rex_command_db_set_connection extends rex_console_command implements rex_command_standalone
{
    protected function configure()
    {
        $this
            ->setDescription('Sets database connection credentials.')
            ->setHelp('Checks by default if a database connection can be established with the new settings.')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'database host')
            ->addOption('login', null, InputOption::VALUE_REQUIRED, 'database user')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'database password')
            ->addOption('database', null, InputOption::VALUE_REQUIRED, 'database name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Save credentials even if validation fails.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $configFile = rex_path::coreData('config.yml');
        $config = rex_file::getConfig($configFile);

        $db = ($config['db'][1] ?? []) + ['host' => '', 'login' => '', 'password' => '', 'name' => ''];

        $changed = false;
        if (null !== $host = $input->getOption('host')) {
            $db['host'] = $host;
            $changed = true;
        }
        if (null !== $login = $input->getOption('login')) {
            $db['login'] = $login;
            $changed = true;
        }
        if (null !== $password = $input->getOption('password')) {
            $db['password'] = $password;
            $changed = true;
        }
        if (null !== $database = $input->getOption('database')) {
            $db['name'] = $database;
            $changed = true;
        }

        if (!$changed) {
            throw new InvalidArgumentException('No database settings given.');
        }

        $settingsValid = rex_sql::checkDbConnection(
            $db['host'],
            $db['login'],
            $db['password'],
            $db['name'],
            false
        );

        if (true !== $settingsValid) {
            $io->error("Can't connect to database:\n" . $settingsValid);

            if (!$input->getOption('force')) {
                return 1;
            }
        } else {
            $io->success('Credentials successfully validated.');
        }

        $config['db'][1] = $db;

        if (rex_file::putConfig($configFile, $config)) {
            $io->success('Database settings successfully saved');
            return 0;
        }
        $io->error('Database settings could\'t be saved.');
        return 1;
    }
}
