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
Can be used like this:

  <info>%command.full_name% | xargs -o mysql</info>
EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = rex::getProperty('db')[1];

        $output->writeln([
            '--host='.escapeshellarg($db['host']),
            '--user='.escapeshellarg($db['login']),
            '--password='.escapeshellarg($db['password']),
            '--database='.escapeshellarg($db['name']),
        ]);
    }
}
