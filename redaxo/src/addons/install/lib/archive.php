<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_install_archive
{
    public static function extract($archive, $dir, $basename = '')
    {
        $dir = rtrim($dir, '/\\');
        rex_dir::delete($dir);

        if (!class_exists('ZipArchive')) {
            $archive = 'phar://' . $archive . '/' . $basename;
            return rex_dir::copy($archive, $dir);
        }

        $zip = new ZipArchive();
        if (!$zip->open($archive)) {
            return false;
        }

        try {
            if ('' === $basename) {
                return $zip->extractTo($dir);
            }

            $tempdir = $dir . '.temp';
            rex_dir::delete($tempdir);

            try {
                if (!$zip->extractTo($tempdir)) {
                    return false;
                }

                if (is_dir($tempdir . '/' . $basename)) {
                    return rename($tempdir . '/' . $basename, $dir);
                }
            } finally {
                rex_dir::delete($tempdir);
            }
        } finally {
            $zip->close();
        }

        return false;
    }

    public static function copyDirToArchive($dir, $archive, $basename = null, $exclude = null)
    {
        $dir = rtrim($dir, '/\\');
        $basename = $basename ?: basename($dir);
        rex_dir::create(dirname($archive));
        $files = [];
        $iterator = rex_finder::factory($dir)->recursive()->filesOnly();
        if ($exclude) {
            $iterator->ignoreDirs($exclude, false);
            $iterator->ignoreFiles($exclude, false);
        }
        foreach ($iterator as $path => $file) {
            $subpath = str_replace($dir, $basename, $path);
            $subpath = str_replace('\\', '/', $subpath);
            $files[$subpath] = $path;
        }
        if (class_exists('ZipArchive')) {
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
}
