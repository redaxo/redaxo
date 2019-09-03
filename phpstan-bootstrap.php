<?php

unset($REX);
$REX['REDAXO'] = false;
$REX['HTDOCS_PATH'] = './';
$REX['BACKEND_FOLDER'] = 'redaxo';
$REX['LOAD_PAGE'] = false;

require $REX['BACKEND_FOLDER'] . '/src/core/boot.php';

// include all functions, which might otherwise only be conditionally included
$finder = rex_finder::factory('redaxo/src/')
    ->recursive()
    // ignore files, which dont contain declarations but execute logic
    ->ignoreFiles('function_rex_category.php')
    ->filesOnly();
/** @var SplFileInfo $file */
foreach ($finder as $path => $file) {
    if (false !== strpos($file->getPath(), 'functions') && '.php' === substr($path, -4)) {
        require_once $file->getRealPath();
    }
}

// manually include functions which dont match the expectations of a "functions" folder
require_once 'redaxo/src/addons/metainfo/extensions/extension_cleanup.php';
