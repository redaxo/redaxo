<?php

unset($REX);
$REX['REDAXO'] = false;
$REX['HTDOCS_PATH'] = './';
$REX['BACKEND_FOLDER'] = 'redaxo';
$REX['LOAD_PAGE'] = false;

require $REX['BACKEND_FOLDER'] . '/src/core/boot.php';

// include all functions, which might otherwise only be conditionally be included
$finder = rex_finder::factory('redaxo/src/')
    ->recursive()
    // ignore files, which dont contain declarations execute logic
    ->ignoreFiles('function_rex_category.php')
    ->filesOnly();
/** @var SplFileInfo $file */
foreach ($finder as $path => $file) {
    if (strpos($file->getPath(), 'functions') !== false) {
        require_once $file->getRealPath();
    }
}
