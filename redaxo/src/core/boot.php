<?php

/**
 * REDAXO Master File.
 *
 * @global string  $REX['HTDOCS_PATH']    [Required] Relative path to htdocs directory
 * @global string  $REX['BACKEND_FOLDER'] [Required] Name of backend folder
 * @global boolean $REX['REDAXO']         [Required] Backend/Frontend flag
 * @global boolean $REX['LOAD_PAGE']      [Optional] Wether the front controller should be loaded or not. Default value is false.
 */

define('REX_MIN_PHP_VERSION', '5.5.9');

if (version_compare(PHP_VERSION, REX_MIN_PHP_VERSION) < 0) {
    throw new Exception('PHP version >=' . REX_MIN_PHP_VERSION . ' needed!');
}

foreach (array('HTDOCS_PATH', 'BACKEND_FOLDER', 'REDAXO') as $key) {
    if (!isset($REX[$key])) {
        throw new Exception('Missing required global variable $REX[\'' . $key . "']");
    }
}

// start output buffering as early as possible, so we can be sure
// we can set http header whenever we want/need to
ob_start();
ob_implicit_flush(0);

// deactivate session cache limiter
session_cache_limiter(false);

// set arg_separator to get valid html output if session.use_trans_sid is activated
ini_set('arg_separator.output', '&amp;');
// make Whoops link to the php.net manual on exception pages, when not configured differently
if (ini_get('html_errors') && !ini_get('docref_root')) {
    ini_set('docref_root', "https://php.net/manual/");
}

require_once __DIR__ . '/lib/util/path.php';

if (isset($REX['PATH_PROVIDER']) && is_object($REX['PATH_PROVIDER'])) {
    $pathProvider = $REX['PATH_PROVIDER'];
} else {
    require_once __DIR__ . '/lib/util/path_default_provider.php';
    $pathProvider = new rex_path_default_provider($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER'], true);
}

rex_path::init($pathProvider);

require_once rex_path::core('lib/autoload.php');

// register core-classes as php-handlers
rex_autoload::register();
// add core base-classpath to autoloader
rex_autoload::addDirectory(rex_path::core('lib'));

// must be called after `rex_autoload::register()` to support symfony/polyfill-mbstring
mb_internal_encoding('UTF-8');

if (isset($REX['URL_PROVIDER']) && is_object($REX['URL_PROVIDER'])) {
    $urlProvider = $REX['URL_PROVIDER'];
} else {
    $urlProvider = new rex_path_default_provider($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER'], false);
}

rex_url::init($urlProvider);

// start timer at the very beginning
rex::setProperty('timer', new rex_timer($_SERVER['REQUEST_TIME_FLOAT']));
// add backend flag to rex
rex::setProperty('redaxo', $REX['REDAXO']);
// add core lang directory to rex_i18n
rex_i18n::addDirectory(rex_path::core('lang'));
// add core base-fragmentpath to fragmentloader
rex_fragment::addDirectory(rex_path::core('fragments/'));

// ----------------- FUNCTIONS
require_once rex_path::core('functions/function_rex_escape.php');
require_once rex_path::core('functions/function_rex_globals.php');
require_once rex_path::core('functions/function_rex_other.php');

// ----------------- VERSION
rex::setProperty('version', '5.6.4');

$cacheFile = rex_path::coreCache('config.yml.cache');
$configFile = rex_path::coreData('config.yml');
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
    if (in_array($key, array('fileperm', 'dirperm'))) {
        $value = octdec($value);
    }
    rex::setProperty($key, $value);
}

date_default_timezone_set(rex::getProperty('timezone', 'Europe/Berlin'));

if (!rex::isSetup()) {
    rex_error_handler::register();
}

rex_var_dumper::register();

// ----------------- REX PERMS

rex_complex_perm::register('clang', 'rex_clang_perm');

// ----- SET CLANG
if (!rex::isSetup()) {
    $clangId = rex_request('clang', 'int', rex_clang::getStartId());
    if (rex::isBackend() || rex_clang::exists($clangId)) {
        rex_clang::setCurrentId($clangId);
    }
}

// ----------------- HTTPS REDIRECT
if ('cli' !== PHP_SAPI && !rex::isSetup()) {
    if ((true === rex::getProperty('use_https') || rex::getEnvironment() === rex::getProperty('use_https')) && !rex_request::isHttps()) {
        rex_response::enforceHttps();
    }

    if (true === rex::getProperty('use_hsts') && rex_request::isHttps()) {
        rex_response::setHeader('Strict-Transport-Security', 'max-age=31536000');
    }
}

if (isset($REX['LOAD_PAGE']) && $REX['LOAD_PAGE']) {
    unset($REX);
    require rex_path::core(rex::isBackend() ? 'backend.php' : 'frontend.php');
}
