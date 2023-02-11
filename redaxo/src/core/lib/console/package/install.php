<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_command_package_install extends rex_console_command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Installs the selected package')
            ->addArgument('package-id', InputArgument::REQUIRED, 'The id of the package (addon or plugin); e.g. "cronjob" or "structure/content"', null, static function () {
                $packageNames = [];

                foreach (rex_package::getRegisteredPackages() as $package) {
                    // allow all packages, because we support --re-intall for already installed ones
                    $packageNames[] = $package->getPackageId();
                }

                return $packageNames;
            })
            ->addOption('re-install', '-r', InputOption::VALUE_NONE, 'Allows to reinstall the Package without asking the User');
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
            $io->error('Package "'.$packageId.'" doesn\'t exists!');
            return 1;
        }

        if ($package->isInstalled() && !$input->getOption('re-install')) {
            $helper = $this->getHelper('question');
            $question = new \Symfony\Component\Console\Question\ConfirmationQuestion('Package "'.$package->getPackageId().'" is already installed. Should it be reinstalled? (y/n) ', false);
            if (!$helper->ask($input, $output, $question)) {
                $io->success('Package "'.$package->getPackageId().'" wasn\'t reinstalled');
                return 0;
            }
        }

        $manager = rex_package_manager::factory($package);
        $success = $manager->install();
        $message = $this->decodeMessage($manager->getMessage());

        if ($success) {
            $io->success($message);
            return 0;
        }

        $io->error($message);
        return 1;
    }
}
