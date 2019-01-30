<?php

$myaddon = rex_addon::get('structure');

// use path relative to __DIR__ to get correct path in update temp dir
$myaddon->includeFile(__DIR__.'/install.php');

foreach ($myaddon->getInstalledPlugins() as $plugin) {
    $file = __DIR__.'/plugins/'.$plugin->getName().'/install.php';

    if (file_exists($file)) {
        $plugin->includeFile($file);
    }
}
