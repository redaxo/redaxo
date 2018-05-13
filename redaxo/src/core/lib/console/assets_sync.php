<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package redaxo\core
 * 
 * @author staabm
 *
 * @internal
 */
class rex_command_assets_sync extends rex_console_command
{
    protected function configure()
    {
        $this
            ->setDescription('Sync folders and files of /assets with /redaxo/src/addons/my-addon/assets (or plugin) respectively /redaxo/src/core/assets folders');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        foreach(rex_package::getInstalledPackages() as $package) {
            if ($package instanceof rex_addon) {
                $assetsPublicPath = $package->getAssetsPath();
                $assetsSrcPath = $package->getPath('assets/');
            } else {
                // we assume "plugin" assets folder is a subfolder of the "addon" assets folder
                // and will therefore already be synced when handling the addon
                continue;
                // $feAssetsPath = rex_path::pluginAssets($package->getAddon()->getName(), $package->getName());
                // $beAssetsPath = rex_path::plugin($package->getAddon()->getName(), $package->getName(), 'assets/');
            }

            // dont create top level "assets" folder when it doesnt exist
            if (!file_exists($assetsPublicPath)) {
                $io->comment("skip not existing assets/ folder: $assetsPublicPath");
                continue;
            }
            if (!file_exists($assetsSrcPath)) {
                $io->comment("skip not existing assets/ folder: $assetsSrcPath");
                continue;
            }

            // sync 1st way, copies ...
            // - existing in FE but not BE
            // - newer in FE then BE
            // - newer in BE then FE
            $this->sync($io, $assetsPublicPath, $assetsSrcPath);
            // sync 2nd way, copies ...
            // - existing in BE but not FE
            $this->sync($io, $assetsSrcPath, $assetsPublicPath);
        }

        $this->sync($io, rex_path::coreAssets(), rex_path::core('assets/'));
    }

    private function sync(SymfonyStyle $io, $folder1, $folder2) {
        foreach(rex_finder::factory($folder1)->recursive()->filesOnly() as $f1Fileinfo) {
            $f1FileName = $f1Fileinfo->getFilename();
            $f1File = (string) $f1Fileinfo;
            $f2File = $folder2 . $f1FileName;
            if (!file_exists($f2File)) {
                rex_file::copy($f1File, $f2File);
                $io->success("created $f2File");
            } else if (is_readable($f1File) && is_readable($f2File) && is_writable($f1File) && is_writable($f2File)) {
                if ($f1Fileinfo->getMtime() > filemtime($f2File)) {
                    rex_file::copy($f1File, $f2File);
                    $io->success("copied $f1File -> $f2File");
                } else if (filemtime($f2File) > $f1Fileinfo->getMtime()) {
                    rex_file::copy($f2File, $f1File);
                    $io->success("copied $f2File -> $f1File");
                } else {
                    // equal modification time, we assume same content
                }
            } else {
                if (!is_readable($f1File)) {
                    $io->error("error $f1File not readable");
                }
                if (!is_readable($f2File)) {
                    $io->error("error $f2File not readable");
                }
                if (!is_writable($f1File)) {
                    $io->error("error $f1File not writable");
                } 
                if (!is_writable($f2File)) {
                    $io->error("error $f2File not writable");
                }
            }
        }
    }
}
