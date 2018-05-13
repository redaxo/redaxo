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
        $created = $updated = $errored = 0;
        $io = $this->getStyle($input, $output);

        foreach(rex_package::getInstalledPackages() as $package) {
            $assetsPublicPath = $package->getAssetsPath();
            $assetsSrcPath = $package->getPath('assets/');

            if (!is_dir($assetsPublicPath) && !is_dir($assetsSrcPath)) {
                continue;
            }
            if (!is_dir($assetsPublicPath)) {
                rex_dir::create($assetsPublicPath);
            }
            if (!is_dir($assetsSrcPath)) {
                rex_dir::create($assetsSrcPath);
            }

            // sync 1st way, copies ...
            // - existing in FE but not "src"
            // - newer in FE then "src"
            // - newer in "src" then FE
            list($ctd, $upd, $err) = $this->sync($io, $assetsPublicPath, $assetsSrcPath);
            $created += $ctd;
            $updated += $upd;
            $errored += $err;

            // sync 2nd way, copies ...
            // - existing in "src" but not FE
            list($ctd, $upd, $err) = $this->sync($io, $assetsSrcPath, $assetsPublicPath);
            $created += $ctd;
            $updated += $upd;
            $errored += $err;
        }

        list($ctd, $upd, $err) = $this->sync($io, rex_path::coreAssets(), rex_path::core('assets/'));
        $created += $ctd;
        $updated += $upd;
        $errored += $err;

        $summary = sprintf('created %s, updated %s file(s) while running into %s errors.', $created, $updated, $errored);
        if ($errored === 0) {
            $io->success($summary);
            return 0;
        }

        $io->error($summary);
        return 1;
    }

    private function sync(SymfonyStyle $io, $folder1, $folder2) {
        $created = $updated = $errored = 0;
        
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
                $created++;
                if($io->isVerbose()) {
                    $io->text("created $f1File -> $f2File");
                }
            } else if (is_readable($f1File) && is_readable($f2File) && is_writable($f1File) && is_writable($f2File)) {
                if ($f1Fileinfo->getMtime() > filemtime($f2File)) {
                    rex_file::copy($f1File, $f2File);
                    $updated++;
                    if($io->isVerbose()) {
                        $io->text("updated $f1File -> $f2File");
                    }
                } else if (filemtime($f2File) > $f1Fileinfo->getMtime()) {
                    rex_file::copy($f2File, $f1File);
                    $updated++;
                    if($io->isVerbose()) {
                        $io->text("updated $f2File -> $f1File");
                    }
                } else {
                    // equal modification time, we assume same content
                }
            } else {
                if (!is_readable($f1File)) {
                    $errored++;
                    if($io->isVerbose()) {
                        $io->error("error $f1File not readable");
                    }
                }
                if (!is_readable($f2File)) {
                    $errored++;
                    if($io->isVerbose()) {
                        $io->error("error $f2File not readable");
                    }
                }
                if (!is_writable($f1File)) {
                    $errored++;
                    if($io->isVerbose()) {
                        $io->error("error $f1File not writable");
                    }
                }
                if (!is_writable($f2File)) {
                    $errored++;
                    if($io->isVerbose()) {
                        $io->error("error $f2File not writable");
                    }
                }
            }
        }
        
        return [$created, $updated, $errored];
    }
}
