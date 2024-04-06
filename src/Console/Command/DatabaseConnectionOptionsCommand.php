<?php

namespace Redaxo\Core\Console\Command;

use Override;
use Redaxo\Core\Core;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class DatabaseConnectionOptionsCommand extends AbstractCommand implements StandaloneInterface
{
    #[Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Dumps the db connection options for the mysql cli tool')
            ->setHelp(<<<'EOF'
                Dumps the db connection options for the <info>mysql</info> cli tool.

                Example: run interactive mysql shell
                  <info>%command.full_name% | xargs -o mysql</info>

                Example: dump the database
                  <info>%command.full_name% | xargs mysqldump > dump.sql</info>

                Example: import a dump file
                  <info>%command.full_name% | xargs sh -c 'mysql "$0" "$@" < dump.sql'</info>
                EOF
            )
        ;
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $db = Core::getDbConfig(1);

        if (!str_contains($db->host, ':')) {
            $output->writeln('--host=' . escapeshellarg($db->host));
        } else {
            [$host, $port] = explode(':', $db->host, 2);

            $output->writeln([
                '--host=' . escapeshellarg($host),
                '--port=' . escapeshellarg($port),
            ]);
        }

        $output->writeln([
            '--user=' . escapeshellarg($db->login),
            '--password=' . escapeshellarg($db->password),
            escapeshellarg($db->name),
        ]);

        return Command::SUCCESS;
    }
}
