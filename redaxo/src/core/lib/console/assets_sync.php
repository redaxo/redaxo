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
            $assetsPublicPath = $package->getAssetsPath();
            $assetsSrcPath = $package->getPath('assets/');

            if (!is_dir($assetsPublicPath) && !is_dir($assetsSrcPath)) {
                continue;
            }

            // sync 1st way, copies ...
            // - existing in FE but not "src"
            // - newer in FE then "src"
            // - newer in "src" then FE
            $this->sync($io, $assetsPublicPath, $assetsSrcPath);
            // sync 2nd way, copies ...
            // - existing in "src" but not FE
            $this->sync($io, $assetsSrcPath, $assetsPublicPath);
        }

        $this->sync($io, rex_path::coreAssets(), rex_path::core('assets/'));
    }

    private function sync(SymfonyStyle $io, $folder1, $folder2) {
        // normalize paths
        $folder1 = realpath($folder1);
        $folder2 = realpath($folder2);

        foreach(rex_finder::factory($folder1)->recursive()->filesOnly() as $f1Fileinfo) {
            $f1File = (string) $f1Fileinfo;
            $relativePath = str_replace($folder1, '', $f1File);
            $f2File = $folder2 . $relativePath;

            if ($f1File === '.redaxo') {
                continue;
            }

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
