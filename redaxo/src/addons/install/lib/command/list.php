<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_command_install_list extends rex_console_command
{
    protected function configure()
    {
        $this->setDescription('List available packages on redaxo.org')
            ->addOption('search', 's', InputOption::VALUE_REQUIRED, 'filter list')
            ->addOption('updates-only', 'u', InputOption::VALUE_NONE, 'only list packages with available updates')
            ->addOption('json', null, InputOption::VALUE_NONE, 'output table as json')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $search = $input->getOption('search');
        $updateOnly = false !== $input->getOption('updates-only');

        $tableHeader = ['key', 'name', 'author', 'last updated', 'latest version', 'installed version'];
        if ($updateOnly) {
            $packages = rex_install_packages::getUpdatePackages();
        } else {
            $packages = rex_install_packages::getAddPackages();
        }

        $rows = [];
        foreach ($packages as $key => $package) {
            $rowData = [
                'key' => $key,
                'name' => strlen($package['name']) > 40 ? substr($package['name'], 0, 40).'...' : $package['name'],
                'author' => $package['author'],
                'last updated' => rex_formatter::strftime($package['updated']),
                'latest version' => reset($package['files'])['version'],
                'installed version' => rex_addon::get($key)->getVersion(),
            ];

            if (null !== $search
                && false === in_array($search, $rowData)
                && false === stripos($package['shortdescription'], $search)) {
                continue;
            }
            $rows[] = $rowData;
        }

        if (false !== $input->getOption('json')) {
            $io->writeln(json_encode($rows));
            return 0;
        }

        $io->table($tableHeader, $rows);
        return 0;
    }
}
