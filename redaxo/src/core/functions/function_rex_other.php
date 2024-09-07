<?php

use Redaxo\Core\Content\StructureElement;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\Finder;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Log\Logger;
use Redaxo\Core\Translation\I18n;

/**
 * Deletes the cache.
 */
function rex_delete_cache(): string
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
    return Extension::registerPoint(new ExtensionPoint('CACHE_DELETED', I18n::msg('delete_cache_message')));
}
