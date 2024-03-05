<?php

$src = __DIR__ . '/redaxo/src';
$tools = __DIR__ . '/.tools';

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        $src . '/core',
        $src . '/addons/debug',
        $src . '/addons/install',
        $src . '/addons/project',
        $tools,
    ])
    ->append([
        __FILE__,
        __DIR__ . '/rector.php',
        $tools . '/bin/clone-addon',
        $tools . '/bin/release',
        $tools . '/bin/update-root-composer',
    ])
;

return (new Redaxo\PhpCsFixerConfig\Config())
    ->setFinder($finder)
;
