<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package redaxo\core
 * 
 * @auth staabm
 *
 * @internal
 */
class rex_command_sync_assets extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('Sync files of /assets with addons/assets core/assets folders');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        foreach(rex_package::getInstalledPackages() as $package) {
            if ($package instanceof rex_addon) {
                $feAssetsPath = rex_path::addonAssets($package->getName());
                $beAssetsPath = rex_path::addon($package->getName(), 'assets/');
            } else {
                // we assume "plugin" assets folder is a subfolder of the "addon" assets folder
                // and will therefore already be synced when handling the addon
                continue;
                // $feAssetsPath = rex_path::pluginAssets($package->getAddon()->getName(), $package->getName());
                // $beAssetsPath = rex_path::plugin($package->getAddon()->getName(), $package->getName(), 'assets/');
            }

            // dont create top level "assets" folder when it doesnt exist
            if (!file_exists($feAssetsPath)) {
                $io->comment("skip not existing assets/ folder: $feAssetsPath");
                continue;
            }
            if (!file_exists($beAssetsPath)) {
                $io->comment("skip not existing assets/ folder: $beAssetsPath");
                continue;
            }

            // sync 1st way, copies ...
            // - existing in FE but not BE
            // - newer in FE then BE
            // - newer in BE then FE
            $this->sync($io, $feAssetsPath, $beAssetsPath);
            // sync 2nd way, copies ...
            // - existing in BE but not FE
            $this->sync($io, $beAssetsPath, $feAssetsPath);
        }

        $this->sync($io, rex_path::coreAssets(), rex_path::core('assets/'));
    }

    private function sync(SymfonyStyle $io, $folder1, $folder2) {
        foreach(new RecursiveDirectoryIterator($folder1, RecursiveDirectoryIterator::SKIP_DOTS) as $feFileinfo) {
            // XXX if($feFileinfo->isLink()) behandeln?

            if ($feFileinfo->isDir()) {
                /*
                skip folders, we assume those could be somehow generated
                $folderName = $feFileinfo->getFilename();

                if (!file_exists($folder2 . $folderName )) {
                    // mkdir($folder2 . $folderName, rex::getDirPerm());
                    echo "mkdir $folder2$folderName\n";
                }
                */

                continue;
            }

            $fileName = $feFileinfo->getFilename();
            $feFile = (string) $feFileinfo;
            $beFile = $folder2 . $fileName;
            if (!file_exists($beFile)) {
                $io->success("created $beFile");
                copy($feFile, $beFile);
            } else if (is_readable($feFile) && is_readable($beFile) && is_writable($feFile) && is_writable($beFile)) {
                if ($feFileinfo->getMtime() > filemtime($beFile)) {
                    $io->success("copied $feFile -> $beFile");
                    rex_file::copy($feFile, $beFile);
                } else if (filemtime($beFile) > $feFileinfo->getMtime()) {
                    $io->success("copied $beFile -> $feFile");
                    rex_file::copy($beFile, $feFile);
                } else {
                    // equal modification time, we assume same content
                }
            } else {
                if (!is_readable($feFile)) {
                    $io->error("error $feFile not readable");
                }
                if (!is_readable($beFile)) {
                    $io->error("error $beFile not readable");
                }
                if (!is_writable($feFile)) {
                    $io->error("error $feFile not writable");                    
                } 
                if (!is_writable($beFile)) {
                    $io->error("error $beFile not writable");
                }
            }
        }
    }
}
