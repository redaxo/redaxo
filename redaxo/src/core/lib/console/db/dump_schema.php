<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\SchemaDumper;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Database\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class rex_command_db_dump_schema extends rex_console_command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Dumps the schema of db tables as php code')
            ->addArgument('table', InputArgument::REQUIRED, 'Database table', null, static function () {
                return Sql::factory()->getTables(Core::getTablePrefix());
            })
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = Table::get($input->getArgument('table'));

        if (!$table->exists()) {
            throw new InvalidArgumentException(sprintf('Table "%s" does not exist.', $table->getName()));
        }

        $generator = new SchemaDumper();

        $output->write($generator->dumpTable($table));

        $io = $this->getStyle($input, $output)->getErrorStyle();
        $io->success('Generated schema for table "' . $table->getName() . '".');

        return 0;
    }
}
