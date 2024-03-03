<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class rex_command_package_activate extends rex_console_command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Activates the selected package')
            ->addArgument('package-id', InputArgument::REQUIRED, 'The id of the addon, e.g. "yform"', null, static function () {
                $packageNames = [];

                foreach (rex_package::getRegisteredPackages() as $package) {
                    if ($package->isAvailable()) {
                        continue;
                    }

                    $packageNames[] = $package->getPackageId();
                }

                return $packageNames;
            });
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);

        $packageId = $input->getArgument('package-id');

        // the package manager don't know new packages in the addon folder
        // so we need to make them available
        rex_package_manager::synchronizeWithFileSystem();

        $package = rex_package::get($packageId);
        if (!$package instanceof rex_package) {
            $io->error('Package "' . $packageId . '" doesn\'t exists!');
            return 1;
        }

        $manager = rex_package_manager::factory($package);
        $success = $manager->activate();
        $message = $this->decodeMessage($manager->getMessage());

        if ($success) {
            $io->success($message);
            return 0;
        }

        $io->error($message);
        return 1;
    }
}
