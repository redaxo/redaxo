<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo/core
 *
 * @internal
 */
class rex_command_install_list extends rex_console_command
{
    protected function configure()
    {
        $this->setDescription('Lists available packages on redaxo.org')
            ->addOption('search', 's', InputOption::VALUE_REQUIRED, 'filter list')
            ->addOption('update-only', 'u', InputOption::VALUE_NONE, 'only list packages with available updates')
            ->addOption('json', null, InputOption::VALUE_NONE, 'output table as json')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $search = $input->getOption('search');
        $updateOnly = false !== $input->getOption('update-only');

        if ($updateOnly) {
            $allPackages = rex_install_packages::getUpdatePackages();
        } else {
            $allPackages = rex_install_packages::getAddPackages();
        }

        $packages = [];
        foreach ($allPackages as $key => $package) {
            $tableHeader = ['key', 'name', 'author', 'last updated'];
            $rowData = [
                'key' => $key,
                'name' => $package['name'],
                'author' => $package['author'],
                'last updated' => rex_formatter::strftime($package['updated']),
            ];

            if ($updateOnly) {
                $tableHeader[] = 'installed version';
                $rowData['installed version'] = rex_addon::get($key)->getVersion();
            }

            $tableHeader[] = 'latest version';
            $rowData['latest version'] = reset($package['files'])['version'];

            if (null !== $search
                && false === in_array($search, $rowData)
                && false === stripos($package['shortdescription'], $search)) {
                continue;
            }
            $packages[] = $rowData;
        }

        if (false !== $input->getOption('json')) {
            $io->writeln(json_encode($packages));
            return 0;
        }

        $io->table($tableHeader, $packages);
        return 0;
    }
}
