<?php

namespace Redaxo\Core\Console\Command;

use Override;
use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Addon\AddonManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class AddonDeactivateCommand extends AbstractCommand
{
    #[Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Deactivates the selected addon')
            ->addArgument('addon-id', InputArgument::REQUIRED, 'The id of the addon, e.g. "yform"', null, static function () {
                $packageNames = [];

                foreach (Addon::getRegisteredAddons() as $package) {
                    if (!$package->isAvailable()) {
                        continue;
                    }

                    $packageNames[] = $package->getPackageId();
                }

                return $packageNames;
            });
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

        $manager = AddonManager::factory($package);
        $success = $manager->deactivate();
        $message = $this->decodeMessage($manager->getMessage());

        if ($success) {
            $io->success($message);
            return Command::SUCCESS;
        }

        $io->error($message);
        return Command::FAILURE;
    }
}
