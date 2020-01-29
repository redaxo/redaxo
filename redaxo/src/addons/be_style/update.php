<?php

$addon = rex_addon::get('be_style');

// use path relative to __DIR__ to get correct path in update temp dir
$addon->includeFile(__DIR__.'/install.php');

// update.php only exist in addons and is also responsible for their plugins
foreach ($addon->getInstalledPlugins() as $plugin) {
    $file = __DIR__.'/plugins/'.$plugin->getName().'/install.php';

    if (file_exists($file)) {
        $plugin->includeFile($file);
    }
}
