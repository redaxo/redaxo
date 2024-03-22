<?php

namespace Redaxo\Core\Console\Command;

use Redaxo\Core\Addon\Addon;
use rex_addon_manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class AddonDeleteCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setDescription('Deletes the selected package')
            ->addArgument('package-id', InputArgument::REQUIRED, 'The id of the addon, e.g. "yform"', null, static function () {
                $packageNames = [];

                foreach (Addon::getRegisteredAddons() as $package) {
                    if ($package->isSystemPackage()) {
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
        rex_addon_manager::synchronizeWithFileSystem();

        $package = Addon::get($packageId);
        if (!$package instanceof Addon) {
            $io->error('Package "' . $packageId . '" doesn\'t exists!');
            return 1;
        }

        $manager = rex_addon_manager::factory($package);
        $success = $manager->delete();
        $message = $this->decodeMessage($manager->getMessage());

        if ($success) {
            $io->success($message);
            return 0;
        }

        $io->error($message);
        return 1;
    }
}
