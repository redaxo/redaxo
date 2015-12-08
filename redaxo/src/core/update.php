<?php

if (rex_string::versionCompare(rex::getVersion(), '5.0.0-beta1', '<=')) {
    rex_file::delete(rex_path::assets('jquery.min.js'));
    rex_file::delete(rex_path::assets('jquery.min.map'));
    rex_file::delete(rex_path::assets('jquery-pjax.min.js'));
    rex_file::delete(rex_path::assets('jquery-ui.custom.min.js'));
    rex_file::delete(rex_path::assets('jquery-ui.custom.txt'));
    rex_file::delete(rex_path::assets('redaxo-logo.svg'));
    rex_file::delete(rex_path::assets('sha1.js'));
    rex_file::delete(rex_path::assets('standard.js'));

    rex_dir::copy(__DIR__ . '/assets', rex_path::assets('core'));

    rex_dir::create(rex_path::data('core'));
    rename(rex_path::data('config.yml'), rex_path::data('core/config.yml'));
}
