<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_command_user_delete extends rex_console_command
{
    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument('user', InputArgument::REQUIRED, 'Username', null, static function () {
                return array_column(rex_sql::factory()->getArray('SELECT login FROM ' . rex::getTable('user')), 'login');
            })
            ->setDescription('Deletes an user by the specified login name.');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);

        $username = $input->getArgument('user');

        $user = rex_sql::factory();
        $user
            ->setTable(rex::getTablePrefix() . 'user')
            ->setWhere(['login' => $username])
            ->select();

        if (!$user->getRows()) {
            $io->error(sprintf('The user "%s" does not exist.', $username));
            return Command::INVALID;
        }

        $askConfirmationQuestion = $io->confirm(sprintf('Are you sure you would like to delete user "%s"?', $username), false);
        if ($askConfirmationQuestion) {
            $this->deleteUserByGivenLoginName($username);
            $io->success(sprintf('User "%s" has been successfully deleted.', $username));
        } else {
            $io->info(sprintf('Aborted. User "%s" was not deleted.', $username));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function deleteUserByGivenLoginName(string $username): void
    {
        $user = rex_sql::factory();
        $user->setTable(rex::getTablePrefix() . 'user');
        $user->setWhere(['login' => $username])->delete();
    }
}
