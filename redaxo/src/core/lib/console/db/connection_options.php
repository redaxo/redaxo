<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @author gharlan
 *
 * @internal
 */
class rex_command_db_connection_options extends rex_console_command
{
    protected function configure()
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = rex::getProperty('db')[1];

        if (false === strpos($db['host'], ':')) {
            $output->writeln('--host='.escapeshellarg($db['host']));
        } else {
            [$host, $port] = explode(':', $db['host'], 2);

            $output->writeln([
                '--host='.escapeshellarg($host),
                '--port='.escapeshellarg($port),
            ]);
        }

        $output->writeln([
            '--user='.escapeshellarg($db['login']),
            '--password='.escapeshellarg($db['password']),
            escapeshellarg($db['name']),
        ]);

        return 0;
    }
}
