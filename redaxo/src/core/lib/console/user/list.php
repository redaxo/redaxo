<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @internal
 */
final class rex_command_user_list extends rex_console_command
{
    #[Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Choose between list all users, or only one by the given login name')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'List all available users')
            ->addArgument('user', InputArgument::OPTIONAL, 'Username', null, static function () {
                return array_column(rex_sql::factory()->getArray('SELECT login FROM' . rex::getTable('user')), 'login');
            });
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);
        $allOption = $input->getOption('all');
        $username = $input->getArgument('user');

        if ($allOption) {
            $this->listAllUsers($output);
            return Command::SUCCESS;
        }

        if ($username) {
            $user = rex_sql::factory();
            $user->setQuery('SELECT `name`, `login`, `email`, `admin`, `createdate`, `lastlogin` FROM ' . rex::getTable('user') . ' WHERE login = :login', [
                'login' => $username,
            ]);

            if (0 === $user->getRows()) {
                $io->error(sprintf('The user "%s" does not exist.', $username));
                return Command::INVALID;
            }

            $userRows = [];
            $userRows[] = [
                $user->getValue('name'),
                $user->getValue('login'),
                $user->getValue('email') ?: 'No E-Mail Configured',
                $user->getValue('admin'),
                $user->getValue('createdate'),
                $user->getValue('lastlogin'),
            ];

            $table = new Table($output);
            $table
                ->setHeaders(['Name', 'Login', 'E-Mail', 'Admin', 'Creation date', 'Last Login'])
                ->setRows($userRows)
                ->render();
            return Command::SUCCESS;
        }

        $io->error('Please specify either "--all" or a login name.');
        return Command::FAILURE;
    }

    private function listAllUsers(OutputInterface $output): void
    {
        $userRows = [];
        $allUsers = rex_sql::factory();
        $allUsers->setQuery('SELECT `name`, `login`, `email`, `admin`, `createdate`, `lastlogin` FROM ' . rex::getTable('user'));
        foreach ($allUsers as $user) {
            $userRows[] = [
                $user->getValue('name'),
                $user->getValue('login'),
                $user->getValue('email') ?: 'No E-Mail configured',
                $user->getValue('admin'),
                $user->getValue('createdate'),
                $user->getValue('lastlogin'),
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Name', 'Login', 'E-Mail', 'Admin', 'Creation date', 'Last Login'])
            ->setRows($userRows)
            ->render();
    }
}
