<?php

namespace Redaxo\Core\Console\Command;

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Addon\AddonManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @internal
 */
class AddonInstallCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setDescription('Installs the selected package')
            ->addArgument('package-id', InputArgument::REQUIRED, 'The id of the addon, e.g. "yform"', null, static function () {
                $packageNames = [];

                foreach (Addon::getRegisteredAddons() as $package) {
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
        AddonManager::synchronizeWithFileSystem();

        $package = Addon::get($packageId);
        if (!$package instanceof Addon) {
            $io->error('Package "' . $packageId . '" doesn\'t exists!');
            return 1;
        }

        if ($package->isInstalled() && !$input->getOption('re-install')) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Package "' . $package->getPackageId() . '" is already installed. Should it be reinstalled? (y/n) ', false);
            if (!$helper->ask($input, $output, $question)) {
                $io->success('Package "' . $package->getPackageId() . '" wasn\'t reinstalled');
                return 0;
            }
        }

        $manager = AddonManager::factory($package);
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
