<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @author gharlan
 *
 * @internal
 */
class rex_console_command_sql_table_generate_code extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('Generates the PHP code for a rex_sql_table definition')
            ->addArgument('table', InputArgument::REQUIRED, 'Database table')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = rex_sql_table::get($input->getArgument('table'));

        $generator = new rex_sql_table_code_generator();

        $output->write($generator->generate($table));
    }
}
