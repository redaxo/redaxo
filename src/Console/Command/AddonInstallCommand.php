<?php

namespace Redaxo\Core\Console\Command;

use Override;
use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Addon\AddonManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
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
    #[Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Installs the selected addon')
            ->addArgument('addon-id', InputArgument::REQUIRED, 'The id of the addon, e.g. "yform"', null, static function () {
                $packageNames = [];

                foreach (Addon::getRegisteredAddons() as $package) {
                    // allow all packages, because we support --re-intall for already installed ones
                    $packageNames[] = $package->getPackageId();
                }

                return $packageNames;
            })
            ->addOption('re-install', '-r', InputOption::VALUE_NONE, 'Allows to reinstall the addon without asking the User');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getStyle($input, $output);

        $packageId = $input->getArgument('addon-id');

        // the package manager don't know new packages in the addon folder
        // so we need to make them available
        AddonManager::synchronizeWithFileSystem();

        $package = Addon::get($packageId);
        if (!$package instanceof Addon) {
            $io->error('Addon "' . $packageId . '" doesn\'t exists!');
            return Command::FAILURE;
        }

        if ($package->isInstalled() && !$input->getOption('re-install')) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Addon "' . $package->getPackageId() . '" is already installed. Should it be reinstalled? (y/n) ', false);
            if (!$helper->ask($input, $output, $question)) {
                $io->success('Addon "' . $package->getPackageId() . '" wasn\'t reinstalled');
                return Command::SUCCESS;
            }
        }

        $manager = AddonManager::factory($package);
        $success = $manager->install();
        $message = $this->decodeMessage($manager->getMessage());

        if ($success) {
            $io->success($message);
            return Command::SUCCESS;
        }

        $io->error($message);
        return Command::FAILURE;
    }
}
