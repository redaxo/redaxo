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

        foreach (rex_package::getInstalledPackages() as $package) {
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
            [$ctd, $upd, $err] = $this->sync($io, $assetsPublicPath, $assetsSrcPath);
            $created += $ctd;
            $updated += $upd;
            $errored += $err;

            // sync 2nd way, copies ...
            // - existing in "src" but not FE
            // - newer in "src" then FE
            [$ctd, $upd, $err] = $this->sync($io, $assetsSrcPath, $assetsPublicPath);
            $created += $ctd;
            $updated += $upd;
            $errored += $err;
        }

        [$ctd, $upd, $err] = $this->sync($io, rex_path::coreAssets(), rex_path::core('assets/'));
        $created += $ctd;
        $updated += $upd;
        $errored += $err;

        if (0 === $errored) {
            $io->success(sprintf('Created %s and updated %s file(s).', $created, $updated));
            return 0;
        }

        $io->error(sprintf('Created %s, updated %s file(s) while running into %s errors.', $created, $updated, $errored));
        return 1;
    }

    /**
     * @return int[]
     *
     * @psalm-return array{0: int, 1: int, 2: int}
     */
    private function sync(SymfonyStyle $io, $folder1, $folder2)
    {
        $created = $updated = $errored = 0;

        // normalize paths
        $folder1 = realpath($folder1);
        $folder2 = realpath($folder2);

        /** @var rex_finder $finder */
        $finder = rex_finder::factory($folder1)
            ->recursive()
            ->ignoreDirs('plugins')
            ->ignoreFiles('.redaxo')
            ->filesOnly();

        // make sure we dont sync plugin assets into a addons asset-dir
        foreach ($finder as $f1Fileinfo) {
            $f1File = (string) $f1Fileinfo;
            $relativePath = str_replace($folder1, '', $f1File);
            $f2File = $folder2 . $relativePath;

            $f1FileShort = rex_path::relative($f1File);
            $f2FileShort = rex_path::relative($f2File);

            $hasError = false;

            if (!is_readable($f1File)) {
                ++$errored;
                $hasError = true;
                $io->text("<error>Not readable:</error> <comment>$f1FileShort</comment>");
            }
            if (file_exists($f2File) && !is_writable($f2File)) {
                ++$errored;
                $hasError = true;
                $io->text("<error>Not writable:</error> <comment>$f2FileShort</comment>");
            }

            if ($hasError) {
                continue;
            }

            if (!file_exists($f2File)) {
                rex_file::copy($f1File, $f2File);
                ++$created;
                if ($io->isVerbose()) {
                    $io->text("Created <comment>$f2FileShort</comment>");
                }

                continue;
            }

            if ($f1Fileinfo->getMtime() > filemtime($f2File)) {
                rex_file::copy($f1File, $f2File);
                ++$updated;
                if ($io->isVerbose()) {
                    $io->text("Updated <comment>$f2FileShort</comment>");
                }
            }
            // else: $f2 is equal or newer, so no sync in this direction
        }

        return [$created, $updated, $errored];
    }
}
