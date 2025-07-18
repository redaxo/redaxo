<?php

namespace Redaxo\Core\Console\Command;

use Override;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

/**
 * @internal
 */
final class UserListCommand extends AbstractCommand
{
    #[Override]
    protected function configure(): void
    {
        $this
            ->setDescription('List all users or a specific user by login name')
            ->addArgument('user', InputArgument::OPTIONAL, 'Username', null, static function () {
                return array_column(Sql::factory()->getArray('SELECT login FROM' . Core::getTable('user')), 'login');
            });
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);

        $username = $input->getArgument('user');
        $sql = Sql::factory();
        $query = '
            SELECT
                IF(name <> "", name, login) as name,
                `login`,
                `email`,
                IF(`admin`, "Admin", IFNULL((SELECT GROUP_CONCAT(name ORDER BY name SEPARATOR ", ") FROM ' . Core::getTable('user_role') . ' r WHERE FIND_IN_SET(r.id, role)), "")) as role,
                `createdate`,
                `lastlogin`
            FROM ' . Core::getTable('user') . '
        ';
        if ($username) {
            $sql->setQuery($query . ' WHERE login = :login', [
                'login' => $username,
            ]);

            if (0 === $sql->getRows()) {
                $io->error(sprintf('The user "%s" does not exist.', $username));
                return Command::FAILURE;
            }
        } else {
            $sql->setQuery($query . ' ORDER BY name');
        }

        $table = new Table($output);
        $table->setHeaders(['Name', 'Login', 'E-Mail', 'Roles', 'Created', 'Last Login']);

        foreach ($sql as $user) {
            $table->addRow([
                $user->getValue('name'),
                $user->getValue('login'),
                $user->getValue('email'),
                $user->getValue('role'),
                $user->getValue('createdate'),
                $user->getValue('lastlogin'),
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
