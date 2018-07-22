<?php

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_command_system_report extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('Shows the system report');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $io->title('System report');

        $report = rex_system_report::factory()->get();

        /** @var Table[] $tables */
        $tables = [];
        $maxLabelLength = 0;

        foreach ($report as $groupLabel => $group) {
            $rows = [];

            foreach ($group as $label => $value) {
                if (is_bool($value)) {
                    $value = $value ? 'yes' : 'no';
                }

                $rows[] = [$label, $value];
                $maxLabelLength = max($maxLabelLength, mb_strlen($label));
            }

            $tables[] = $table = new Table($io);
            $table->setHeaders([$groupLabel, '']);
            $table->setRows($rows);
        }

        $style = new TableStyle();

        $leftColumnStyle = clone $style;
        $leftColumnStyle->setPadType(STR_PAD_LEFT);

        foreach ($tables as $table) {
            $table->setColumnWidths([$maxLabelLength, 30]);

            $table->setStyle($style);
            $table->setColumnStyle(0, $leftColumnStyle);

            $table->render();
            $io->newLine();
        }
    }
}
