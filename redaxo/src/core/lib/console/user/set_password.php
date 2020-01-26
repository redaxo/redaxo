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
class rex_command_user_set_password extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('Sets a new password for a user')
            ->addArgument('user', InputArgument::REQUIRED, 'Username')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
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

        if (!$user->getRows()) {
            throw new InvalidArgumentException(sprintf('User "%s" does not exist.', $username));
        }

        $user = new rex_user($user);
        $id = $user->getId();

        $passwordPolicy = rex_backend_password_policy::factory(rex::getProperty('password_policy', []));

        $password = $input->getArgument('password');

        if ($password && true !== $msg = $passwordPolicy->check($password, $id)) {
            throw new InvalidArgumentException($msg);
        }

        if (!$password) {
            $password = $io->askHidden('Password', static function ($password) use ($id, $passwordPolicy) {
                if (true !== $msg = $passwordPolicy->check($password, $id)) {
                    throw new InvalidArgumentException($msg);
                }

                return $password;
            });
        }

        if (!$password) {
            throw new InvalidArgumentException('Missing password.');
        }

        rex_sql::factory()
            ->setTable(rex::getTable('user'))
            ->setWhere(['id' => $id])
            ->setValue('password', rex_backend_login::passwordHash($password))
            ->addGlobalUpdateFields('console')
            ->update();

        rex_extension::registerPoint(new rex_extension_point('PASSWORD_UPDATED', '', [
            'user_id' => $id,
            'user' => $user,
            'password' => $password,
        ], true));

        $io->success(sprintf('Saved new password for user "%s".', $username));

        return 0;
    }
}
