<?php

/**
 * Functions
 * @package redaxo5
 */

/**
 * Deletes the cache
 *
 * @package redaxo\core
 */
function rex_delete_cache()
{
    // close logger, so the logfile can also be deleted
    rex_logger::close();

    $finder = rex_finder::factory(rex_path::cache())
        ->recursive()
        ->childFirst()
        ->ignoreFiles(array('.htaccess', '.redaxo'), false)
        ->ignoreSystemStuff(false);
    rex_dir::deleteIterator($finder);

    rex_clang::reset();

    // ----- EXTENSION POINT
    return rex_extension::registerPoint('CACHE_DELETED', rex_i18n::msg('delete_cache_message'));
}

/**
 * @param string $val
 * @return mixed
 * @package redaxo\core
 */
function rex_ini_get($val)
{
    $val = trim(ini_get($val));
    if ($val != '') {
        $last = strtolower($val{
            strlen($val) - 1
        });
    } else {
        $last = '';
    }
    switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                    $val *= 1024;
            case 'm':
                    $val *= 1024;
            case 'k':
                    $val *= 1024;
    }

    return $val;
}
