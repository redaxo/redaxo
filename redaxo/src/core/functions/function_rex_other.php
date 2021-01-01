<?php

/**
 * Functions.
 *
 * @package redaxo5
 */

/**
 * Deletes the cache.
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
        ->ignoreFiles(['.htaccess', '.redaxo'], false)
        ->ignoreSystemStuff(false);
    rex_dir::deleteIterator($finder);

    rex_clang::reset();

    if (function_exists('opcache_reset')) {
        opcache_reset();
    }

    // ----- EXTENSION POINT
    return rex_extension::registerPoint(new rex_extension_point('CACHE_DELETED', rex_i18n::msg('delete_cache_message')));
}

/**
 * @param string $varname
 *
 * @return int
 *
 * @package redaxo\core
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

/**
 * @internal
 */
function packages_title()
{
    $package = rex_package::get(rex_request('package', 'string'));
    $subPage = rex_request('subpage', 'string');
    $packageId = $package->getPackageId();

    $hasChangelog = is_readable($package->getPath('CHANGELOG.md'));

    $navigation = [
        ['href' => rex_url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]), 'title' => rex_i18n::msg('package_hhelp') . ' / ' . rex_i18n::msg('credits')],
        ['href' => rex_url::currentBackendPage(['subpage' => 'changelog', 'package' => $packageId]), 'title' => rex_i18n::msg('credits_changelog')],
        ['href' => rex_url::currentBackendPage(['subpage' => 'license', 'package' => $packageId]), 'title' => rex_i18n::msg('credits_license')],
    ];

    switch ($subPage) {
        case 'help':
            $navigation[0]['active'] = true;
            break;
        case 'changelog':
            $navigation[1]['active'] = true;
            break;
        case 'license':
            $navigation[2]['active'] = true;
            break;
    }

    if (!$hasChangelog) {
        unset($navigation[1]);
    }

    $fragment = new rex_fragment();
    $fragment->setVar('left', $navigation, false);
    $subtitle = $fragment->parse('core/navigations/content.php');

    $headLine = 'AddOn: '. $packageId;
    if ($package instanceof rex_plugin_interface) {
        $headLine = 'PlugIn: '. $packageId;
    }

    echo rex_view::title($headLine, $subtitle);
}
