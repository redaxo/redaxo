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
            ->addArgument('login', InputArgument::REQUIRED, 'Login')
            ->setDescription('Deletes an administrator user by the specified login name.');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);

        $loginName = $input->getArgument('login');

        $user = rex_sql::factory();
        $user
            ->setTable(rex::getTablePrefix() . 'user')
            ->setWhere(['login' => $loginName])
            ->select();

        if (!$user->getRows()) {
            $io->error(sprintf('The admin user "%s" does not exist.', $loginName));
            return COMMAND::INVALID;
        }

        $askConfirmationQuestion = $io->confirm(sprintf('Are you sure you would like to delete user "%s"?', $loginName), false);
        if ($askConfirmationQuestion) {
            $this->deleteAdminUserByGivenLoginName($loginName);
            $io->success(sprintf('User "%s" has been successfully deleted.', $loginName));
        } else {
            $io->info(sprintf('Aborted. User "%s" was not deleted.', $loginName));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function deleteAdminUserByGivenLoginName(string $loginName): void
    {
        $user = rex_sql::factory();
        $user->setTable(rex::getTablePrefix() . 'user');
        $user->setWhere(['login' => $loginName])->delete();
    }
}
