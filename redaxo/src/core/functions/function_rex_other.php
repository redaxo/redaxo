<?php

use Redaxo\Core\Content\StructureElement;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\Finder;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Log\Logger;
use Redaxo\Core\Translation\I18n;

/**
 * Deletes the cache.
 *
 * @return string
 */
function rex_delete_cache()
{
    // close logger, so the logfile can also be deleted
    Logger::close();

    $finder = Finder::factory(Path::cache())
        ->recursive()
        ->childFirst()
        ->ignoreFiles(['.htaccess', '.redaxo'], false)
        ->ignoreSystemStuff(false);
    Dir::deleteIterator($finder);

    Language::reset();

    StructureElement::clearInstancePool();
    StructureElement::clearInstanceListPool();
    StructureElement::resetClassVars();

    if (function_exists('opcache_reset')) {
        opcache_reset();
    }

    // ----- EXTENSION POINT
    return rex_extension::registerPoint(new rex_extension_point('CACHE_DELETED', I18n::msg('delete_cache_message')));
}

/**
 * @param string $varname
 *
 * @return int
 */
function rex_ini_get($varname)
{
    $val = trim(ini_get($varname));
    if ('' != $val) {
        $last = strtolower($val[strlen($val) - 1]);
    } else {
        $last = '';
    }
    $val = (int) $val;
    switch ($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
            // no break
        case 'm':
            $val *= 1024;
            // no break
        case 'k':
            $val *= 1024;
    }

    return $val;
}
