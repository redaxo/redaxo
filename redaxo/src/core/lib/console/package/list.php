<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_command_package_list extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('List available packages')
            ->addOption('search', 's', InputOption::VALUE_REQUIRED, 'filter list')
            ->addOption('package-id', 'p', InputOption::VALUE_REQUIRED, 'search for exactly this package-id ')
            ->addOption('installed-only', 'i', InputOption::VALUE_NONE, 'only list installed packages')
            ->addOption('activated-only', 'a', InputOption::VALUE_NONE, 'only list active packages')
            ->addOption('error-when-empty', null, InputOption::VALUE_NONE, 'if no package matches your filter the command exits with error-code 1, otherwise with 0')
            ->addOption('json', null, InputOption::VALUE_NONE, 'output table as json')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        // the package manager don't know new packages in the addon folder
        // so we need to make them available
        rex_package_manager::synchronizeWithFileSystem();

        $search = $input->getOption('search');
        $packageId = $input->getOption('package-id');

        $installedOnly = false !== $input->getOption('installed-only');
        $activatedOnly = false !== $input->getOption('activated-only');
        $jsonOutput = false !== $input->getOption('json');
        $usingExitCode = false !== $input->getOption('error-when-empty');

        $packages = rex_package::getRegisteredPackages();

        $rows = [];
        foreach ($packages as $package) {
            $rowdata = [
                'package-id' => $package->getPackageId(),
                'author' => $package->getAuthor(),
                'version' => $package->getVersion(),
                'activated' => $package->isAvailable(),
                'installed' => $package->isInstalled(),
            ];

            if (!$jsonOutput) {
                $rowdata['activated'] = $rowdata['activated'] ? 'x' : '';
                $rowdata['installed'] = $rowdata['installed'] ? 'x' : '';
            }

            if(null !== $packageId && $packageId !== $package->getPackageId()) {
                continue;
            }

            if (null !== $search && false === stripos($package->getPackageId(), $search)) {
                continue;
            }

            if ($installedOnly && !$package->isInstalled()) {
                continue;
            }

            if ($activatedOnly && !$package->isAvailable()) {
                continue;
            }

            $rows[] = $rowdata;
        }

        if ($jsonOutput) {
            $io->writeln(json_encode($rows));
            return $usingExitCode ? (int) (0 === count($rows)) : 0;
        }

        $io->table(['package-id', 'author', 'version', 'activated', 'installed'], $rows);
        return $usingExitCode ? (int) (0 === count($rows)) : 0;
    }
}
