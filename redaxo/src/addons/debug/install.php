<?php

$addon = rex_addon::get('debug');
rex_dir::copy(
    $addon->getPath('frontend/web/'),
    $addon->getAssetsPath('clockwork')
);
