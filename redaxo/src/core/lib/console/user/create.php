<?php

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_command_user_create extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('Create a new user')
            ->addArgument('user', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->addArgument('is_admin', InputArgument::OPTIONAL, 'Grant admin permissions', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $username = $input->getArgument('user');

        $user = rex_sql::factory();
        $user
            ->setTable(rex::getTable('user'))
            ->setWhere(['login' => $username])
            ->select();

        if ($user->getRows()) {
            throw new InvalidArgumentException(sprintf('User "%s" already exists.', $username));
        }

        $passwordPolicy = rex_backend_password_policy::factory(rex::getProperty('password_policy', []));

        $password = $input->getArgument('password');
        if ($password && true !== $msg = $passwordPolicy->check($password)) {
            throw new InvalidArgumentException($msg);
        }

        if (!$password) {
            $password = $io->askHidden('Password', function ($password) use ($passwordPolicy) {
                if (true !== $msg = $passwordPolicy->check($password)) {
                    throw new InvalidArgumentException($msg);
                }

                return $password;
            });
        }

        if (!$password) {
            throw new InvalidArgumentException('Missing password.');
        }

        $user = rex_sql::factory();
        // $user->setDebug();
        $user->setTable(rex::getTablePrefix() . 'user');
        $user->setValue('name', 'Administrator');
        $user->setValue('login', $username);
        $user->setValue('password', rex_backend_login::passwordHash($password));
        $user->setValue('admin', $input->getArgument('is_admin') ? 1 : 0);
        $user->addGlobalCreateFields('console');
        $user->setValue('status', '1');
        $user->insert();

        $io->success(sprintf('User "%s" successfully created.', $username));
    }
}
