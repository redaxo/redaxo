<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class rex_command_package_run_update_script extends rex_console_command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Runs the update.php of the given package with given previous version')
            ->addArgument('package-id', InputArgument::REQUIRED, 'The id of the addon, e.g. "yform"', null, static function () {
                $packageNames = [];

                foreach (rex_addon::getRegisteredAddons() as $package) {
                    if (!$package->isInstalled()) {
                        continue;
                    }

                    $packageNames[] = $package->getPackageId();
                }

                return $packageNames;
            })
            ->addArgument('previous-version', InputArgument::REQUIRED, 'The previous package version that is used for version comparisons in update.php')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);

        $packageId = $input->getArgument('package-id');

        $package = rex_addon::get($packageId);
        if (!$package->isInstalled()) {
            $io->error('Package "' . $packageId . '" is not installed!');
            return 1;
        }

        $version = $package->getVersion();
        $package->setProperty('version', $input->getArgument('previous-version'));

        try {
            $package->includeFile(rex_addon::FILE_UPDATE);
        } finally {
            $package->setProperty('version', $version);
        }

        if ('' !== ($message = (string) $package->getProperty('updatemsg', ''))) {
            $io->error($message);
            return 1;
        }
        if (!$package->getProperty('update', true)) {
            $io->error('Failed without a given reason.');
            return 1;
        }

        $io->success('Successfully executed the update.php.');
        return 0;
    }
}
