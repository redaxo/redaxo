<?php

// remove old login background images
$files = glob(rex_path::pluginAssets('be_style', 'redaxo', 'images/*-unsplash*')) ?: [];
foreach ($files as $file) {
    if (!is_file(rex_path::plugin('be_style', 'redaxo', 'assets/images/' . rex_path::basename($file)))) {
        rex_file::delete($file);
    }
}
