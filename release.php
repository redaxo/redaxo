#!/usr/bin/env php
<?php

unset($REX);
$REX['REDAXO'] = true;
$REX['HTDOCS_PATH'] = './';
$REX['BACKEND_FOLDER'] = 'redaxo';
$REX['LOAD_PAGE'] = false;

require $REX['BACKEND_FOLDER'] . '/src/core/boot.php';
require $REX['BACKEND_FOLDER'] . '/src/core/packages.php';

class rex_release
{
    private static $addons = ['backup', 'be_style', 'cronjob', 'debug', 'install', 'media_manager', 'mediapool', 'metainfo', 'phpmailer', 'structure', 'textile', 'users'];

    private $version;

    public function __construct($version)
    {
        $this->version = $version;
    }

    public function create()
    {
        $this->updateVersion();
        $this->createArchives();
    }

    private function updateVersion()
    {
        $file = rex_path::core('boot.php');
        $content = rex_file::get($file);
        $content = preg_replace('/(?<=^rex::setProperty\(\'version\', \').*?(?=\')/m', $this->version, $content);
        rex_file::put($file, $content);
    }

    private function createArchives()
    {
        $path = rex_path::base('releases/'.$this->version.'/');
        rex_dir::deleteFiles($path);
        rex_dir::create($path);

        $complete = new ZipArchive();
        $update = new ZipArchive();

        $complete->open($path.'redaxo_'.$this->version.'.zip', ZipArchive::CREATE);
        $update->open($path.'redaxo_update_'.$this->version.'.zip', ZipArchive::CREATE);

        $files = [
            'assets/.redaxo',
            'media/.redaxo',
            'redaxo/cache/.htaccess',
            'redaxo/cache/.redaxo',
            'redaxo/data/.htaccess',
            'redaxo/data/.redaxo',
            'redaxo/src/.htaccess',
            'redaxo/index.php',
            'index.php',
            'LICENSE.md',
            'README.md',
        ];

        foreach ($files as $file) {
            $complete->addFile(rex_path::base($file), $file);
        }

        $this->addDir($complete, rex_path::core(), 'redaxo/src/core');
        $this->addDir($update, rex_path::core(), 'core');

        foreach (self::$addons as $addon) {
            $this->addDir($complete, rex_path::addon($addon), 'redaxo/src/addons/'.$addon);
            $this->addDir($update, rex_path::addon($addon), 'addons/'.$addon);
        }

        $addon = rex_addon::get('be_style');
        $this->addDir($complete, $addon->getPath('assets'), 'assets/addons/be_style');
        $this->addDir($complete, $addon->getPlugin('redaxo')->getPath('assets'), 'assets/addons/be_style/plugins/redaxo');

        $files = require $addon->getPath('vendor_files.php');
        foreach ($files as $source => $destination) {
            $complete->addFile($addon->getPath($source), 'assets/addons/be_style/'.$destination);
        }

        $complete->close();
        $update->close();
    }

    private function addDir(ZipArchive $zip, $dir, $base)
    {
        $dir = rtrim($dir, '\\/');

        $finder = rex_finder::factory($dir)
            ->recursive()
            ->filesOnly()
        ;

        /** @var SplFileInfo $file */
        foreach ($finder as $path => $file) {
            if (!$this->matchesGitignore($path)) {
                $zip->addFile($path, $base.substr($path, strlen($dir)));
            }
        }
    }

    private function matchesGitignore($path)
    {
        static $gitignore;

        if (null === $gitignore) {
            $gitignore = file(rex_path::base('.gitignore'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $gitignore = array_filter($gitignore, function ($path) {
                return '#' !== $path[0];
            });
        }

        $subpath = substr($path, strlen(rex_path::base()));
        foreach ($gitignore as $ignore) {
            if (0 === strpos($subpath, $ignore)) {
                return true;
            }
        }

        return false;
    }
}

if (!isset($argv[1])) {
    exit('Missing version'.PHP_EOL);
}

$release = new rex_release($argv[1]);
$release->create();
