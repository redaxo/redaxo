<?php

use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\Finder;
use Redaxo\Core\Filesystem\Path;

/**
 * @internal
 */
class rex_install_archive
{
    public static function extract(string $archive, string $dir, string $basename = ''): bool
    {
        $dir = rtrim($dir, '/\\');
        Dir::delete($dir);

        if (!class_exists(ZipArchive::class)) {
            $archive = 'phar://' . $archive . DIRECTORY_SEPARATOR . $basename;
            return Dir::copy($archive, $dir);
        }

        $zip = new ZipArchive();
        if (!$zip->open($archive)) {
            return false;
        }

        try {
            if ('' === $basename) {
                if (!$zip->extractTo($dir)) {
                    return false;
                }

                self::setPermissions($dir);

                return true;
            }

            $tempdir = $dir . '.temp';
            Dir::delete($tempdir);

            try {
                if (!$zip->extractTo($tempdir)) {
                    return false;
                }

                if (!is_dir($tempdir . DIRECTORY_SEPARATOR . $basename) || !rename($tempdir . DIRECTORY_SEPARATOR . $basename, $dir . DIRECTORY_SEPARATOR)) {
                    return false;
                }

                self::setPermissions($dir);
            } finally {
                Dir::delete($tempdir);
            }
        } finally {
            $zip->close();
        }

        return true;
    }

    /**
     * @param string|list<string>|null $exclude
     * @return void
     */
    public static function copyDirToArchive(string $dir, string $archive, ?string $basename = null, $exclude = null)
    {
        $dir = rtrim($dir, '/\\');
        $basename = $basename ?: Path::basename($dir);
        Dir::create(dirname($archive));
        $files = [];
        $iterator = Finder::factory($dir)->recursive()->filesOnly();
        if ($exclude) {
            $iterator->ignoreDirs($exclude, false);
            $iterator->ignoreFiles($exclude, false);
        }
        foreach ($iterator as $path => $_) {
            $subpath = str_replace($dir, $basename, $path);
            $subpath = str_replace('\\', '/', $subpath);
            $files[$subpath] = $path;
        }
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            $zip->open($archive, ZipArchive::CREATE);
            foreach ($files as $path => $realpath) {
                $zip->addFile($realpath, $path);
            }
            $zip->close();
        } else {
            $phar = new PharData($archive, 0, null, Phar::ZIP);
            $phar->buildFromIterator(new ArrayIterator($files));
            $phar->compressFiles(Phar::GZ);
            foreach ($files as $path => $realpath) {
                if (0 == filesize($realpath)) {
                    $phar[$path]->decompress();
                }
            }
        }
    }

    private static function setPermissions(string $dir): void
    {
        @chmod($dir, Core::getDirPerm());

        $finder = Finder::factory($dir)->recursive();

        foreach ($finder as $path => $file) {
            @chmod($path, $file->isDir() ? Core::getDirPerm() : Core::getFilePerm());
        }
    }
}
