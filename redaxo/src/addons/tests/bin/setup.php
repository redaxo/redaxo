#!/usr/bin/php
<?php

if (PHP_SAPI !== 'cli') {
    echo 'error: this script may only be run from CLI', PHP_EOL;
    return 1;
}

// bring the file into context, no matter from which dir it was executed
$path = explode(DIRECTORY_SEPARATOR, __DIR__);
do {
    $part = array_pop($path);
} while ($part !== null && $part != 'redaxo');

if (!chdir(implode(DIRECTORY_SEPARATOR, $path) . '/redaxo')) {
    echo 'error: start this script within a redaxo projects folder', PHP_EOL;
    return 2;
}

// ---- bootstrap REX

$REX = [];
$REX['REDAXO'] = true;
$REX['HTDOCS_PATH'] = '../';
$REX['BACKEND_FOLDER'] = 'redaxo';

file_put_contents('data/config.yml', "error_email: info@redaxo.org\n");

// bootstrap core
require 'src/core/boot.php';

// bootstrap addons
include_once rex_path::core('packages.php');

// run setup, if instance not already prepared
if (rex::isSetup()) {
    $err = '';

    // read initial config
    $configFile = rex_path::coreData('config.yml');
    $config = array_merge(
        rex_file::getConfig(rex_path::core('default.config.yml')),
        rex_file::getConfig($configFile)
    );

    // init db
    $err .= rex_setup::checkDb($config, false);
    $err .= rex_setup_importer::prepareEmptyDb();
    $err .= rex_setup_importer::verifyDbSchema();

    if ($err != '') {
        echo $err;
        exit(10);
    }

    // install tests addon
    $manager = rex_addon_manager::factory(rex_addon::get('tests'));
    $manager->install() || $err .= $manager->getMessage();
    $manager->activate() || $err .= $manager->getMessage();

    if ($err != '') {
        echo $err;
        exit(20);
    }

    $config['setup'] = false;
    if (rex_file::putConfig($configFile, $config)) {
        echo 'instance setup successfull', PHP_EOL;
        exit(0);
    }
    echo 'instance setup failure', PHP_EOL;
    exit(1);
}

echo 'instance setup not necessary', PHP_EOL;
exit(0);
