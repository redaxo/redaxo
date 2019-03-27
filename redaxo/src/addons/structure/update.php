<?php

/** @var rex_addon $this */

// use path relative to __DIR__ to get correct path in update temp dir
$this->includeFile(__DIR__.'/install.php');

foreach ($this->getInstalledPlugins() as $plugin) {
    $file = __DIR__.'/plugins/'.$plugin->getName().'/install.php';

    if (file_exists($file)) {
        $plugin->includeFile($file);
    }
}
