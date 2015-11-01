<?php

/**
 * REDAXO Master File.
 *
 * @global string  $REX['HTDOCS_PATH']    [Required] Relative path to htdocs directory
 * @global string  $REX['BACKEND_FOLDER'] [Required] Name of backend folder
 * @global boolean $REX['REDAXO']         [Required] Backend/Frontend flag
 * @global boolean $REX['LOAD_PAGE']      [Optional] Wether the front controller should be loaded or not. Default value is false.
 *
 * @codingStandardsPhp53
 */

define('REX_MIN_PHP_VERSION', '5.4.0');

if (version_compare(PHP_VERSION, REX_MIN_PHP_VERSION) < 0) {
    throw new Exception('PHP version >=' . REX_MIN_PHP_VERSION . ' needed!');
}

if (!extension_loaded('mbstring')) {
    throw new Exception('PHP extension "mbstring" needed!');
}

foreach (['HTDOCS_PATH', 'BACKEND_FOLDER', 'REDAXO'] as $key) {
    if (!isset($REX[$key])) {
        throw new Exception('Missing required global variable $REX[\'' . $key . "']");
    }
}

// start output buffering as early as possible, so we can be sure
// we can set http header whenever we want/need to
ob_start();
ob_implicit_flush(0);

mb_internal_encoding('UTF-8');

// deactivate session cache limiter
session_cache_limiter(false);

// set arg_separator to get valid html output if session.use_trans_sid is activated
ini_set('arg_separator.output', '&amp;');

require_once __DIR__ . '/lib/util/path.php';
require_once __DIR__ . '/lib/util/path_default_provider.php';
rex_path::init(new rex_path_default_provider($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER'], true));

require_once rex_path::core('lib/autoload.php');

// register core-classes  as php-handlers
rex_autoload::register();
// add core base-classpath to autoloader
rex_autoload::addDirectory(rex_path::core('lib'));

rex_url::init(new rex_path_default_provider($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER'], false));

// start timer at the very beginning
rex::setProperty('timer', new rex_timer($_SERVER['REQUEST_TIME_FLOAT']));
// add backend flag to rex
rex::setProperty('redaxo', $REX['REDAXO']);
// add core lang directory to rex_i18n
rex_i18n::addDirectory(rex_path::core('lang'));
// add core base-fragmentpath to fragmentloader
rex_fragment::addDirectory(rex_path::core('fragments/'));

// ----------------- FUNCTIONS
require_once rex_path::core('functions/function_rex_globals.php');
require_once rex_path::core('functions/function_rex_other.php');

// ----------------- VERSION
rex::setProperty('version', '5.0.0-alpha7');

$cacheFile = rex_path::cache('config.yml.cache');
$configFile = rex_path::data('config.yml');
if (file_exists($cacheFile) && file_exists($configFile) && filemtime($cacheFile) >= filemtime($configFile)) {
    $config = rex_file::getCache($cacheFile);
} else {
    $config = array_merge(
        rex_file::getConfig(rex_path::core('default.config.yml')),
        rex_file::getConfig($configFile)
    );
    rex_file::putCache($cacheFile, $config);
}
foreach ($config as $key => $value) {
    if (in_array($key, ['fileperm', 'dirperm'])) {
        $value = octdec($value);
    }
    rex::setProperty($key, $value);
}

date_default_timezone_set(rex::getProperty('timezone', 'Europe/Berlin'));

if (!rex::isSetup()) {
    rex_error_handler::register();
}

// ----------------- REX PERMS

rex_complex_perm::register('clang', 'rex_clang_perm');

// ----- SET CLANG
if (!rex::isSetup()) {
    rex_clang::setCurrentId(rex_request('clang', 'int', rex_clang::getStartId()));
}

if (isset($REX['LOAD_PAGE']) && $REX['LOAD_PAGE']) {
    unset($REX);
    require rex_path::core(rex::isBackend() ? 'backend.php' : 'frontend.php');
}
