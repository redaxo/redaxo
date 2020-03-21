<?php

$addon = rex_addon::get('debug');
rex_dir::copy(
    $addon->getPath('frontend/web/'),
    $addon->getAssetsPath('clockwork')
);

$indexPath = $addon->getAssetsPath('clockwork/index.html');

$index = file_get_contents($indexPath);
$index = preg_replace('/(href|src)=("?)([^>\s]+)/', '$1=$2'.$addon->getAssetsUrl('clockwork/$3'), $index);
file_put_contents($indexPath, $index);
