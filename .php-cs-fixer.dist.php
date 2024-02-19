<?php

$src = __DIR__ . '/redaxo/src';
$bootPath = realpath($src . '/core/boot.php');
$tools = __DIR__ . '/.tools';

$finder = PhpCsFixer\Finder::create()
    ->in([
        $src . '/core',
        $src . '/addons/debug',
        $src . '/addons/install',
        $src . '/addons/mediapool',
        $src . '/addons/metainfo',
        $src . '/addons/project',
        $src . '/addons/structure',
        $tools,
    ])
    ->append([
        __FILE__,
        __DIR__ . '/rector.php',
        $tools . '/bin/clone-addon',
        $tools . '/bin/release',
        $tools . '/bin/update-root-composer',
    ])
    ->filter(static function (SplFileInfo $file) use ($bootPath) {
        return $bootPath !== $file->getRealPath();
    })
;

return (new Redaxo\PhpCsFixerConfig\Config())
    ->setFinder($finder)
;
