<?php

namespace Redaxo\Core\Console\Command;

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use rex_backend_login;
use rex_backend_password_policy;
use rex_extension;
use rex_extension_point;
use rex_user;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class UserSetPasswordCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setDescription('Sets a new password for a user')
            ->addArgument('user', InputArgument::REQUIRED, 'Username', null, static function () {
                return array_column(Sql::factory()->getArray('SELECT login FROM ' . Core::getTable('user')), 'login');
            })
            ->addArgument('password', InputArgument::OPTIONAL, 'Password')
            ->addOption('password-change-required', null, InputOption::VALUE_NONE, 'Require password change after login')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);

        $username = $input->getArgument('user');

        $user = Sql::factory();
        $user
            ->setTable(Core::getTable('user'))
            ->setWhere(['login' => $username])
            ->select();

        if (!$user->getRows()) {
            throw new InvalidArgumentException(sprintf('User "%s" does not exist.', $username));
        }

        $user = rex_user::fromSql($user);
        $id = $user->getId();

        $passwordPolicy = rex_backend_password_policy::factory();

        $password = $input->getArgument('password');

        if ($password && true !== $msg = $passwordPolicy->check($password, $id)) {
            throw new InvalidArgumentException($msg);
        }

        if (!$password) {
            $description = $passwordPolicy->getDescription();
            $description = $description ? ' (' . $description . ')' : '';

            $password = $io->askHidden('Password' . $description, static function ($password) use ($id, $passwordPolicy) {
                if (true !== $msg = $passwordPolicy->check($password, $id)) {
                    throw new InvalidArgumentException($msg);
                }

                return $password;
            });
        }

        if (!$password) {
            throw new InvalidArgumentException('Missing password.');
        }

        $passwordHash = rex_backend_login::passwordHash($password);

        Sql::factory()
            ->setTable(Core::getTable('user'))
            ->setWhere(['id' => $id])
            ->setValue('password', $passwordHash)
            ->setValue('login_tries', 0)
            ->addGlobalUpdateFields('console')
            ->setDateTimeValue('password_changed', time())
            ->setArrayValue('previous_passwords', $passwordPolicy->updatePreviousPasswords($user, $passwordHash))
            ->setValue('password_change_required', (int) $input->getOption('password-change-required'))
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