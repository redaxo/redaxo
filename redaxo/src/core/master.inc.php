<?php

/**
 * Hauptkonfigurationsdatei
 * @package redaxo4
 */

define('REX_MIN_PHP_VERSION', '5.3.0');

if (version_compare(PHP_VERSION, REX_MIN_PHP_VERSION) < 0) {
  exit('PHP version >=' . REX_MIN_PHP_VERSION . ' needed!');
}

// start output buffering as early as possible, so we can be sure
// we can set http header whenever we want/need to
ob_start();
ob_implicit_flush(0);

mb_internal_encoding('UTF-8');

require_once dirname(__FILE__) . '/lib/path.php';
rex_path::init($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER']);

require_once rex_path::core('lib/autoload.php');

// register core-classes  as php-handlers
rex_autoload::register();
// add core base-classpath to autoloader
rex_autoload::addDirectory(rex_path::core('lib'));
rex_autoload::addDirectory(rex_path::core('vendor'));

rex_url::init($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER']);

// start timer at the very beginning
rex::setProperty('timer', new rex_timer);
// register rex_error_handler
rex_error_handler::register();
// add backend flag to rex
rex::setProperty('redaxo', $REX['REDAXO']);
// reset $REX
unset($REX);
// add core lang directory to rex_i18n
rex_i18n::addDirectory(rex_path::core('lang'));
// add core base-fragmentpath to fragmentloader
rex_fragment::addDirectory(rex_path::core('fragments/'));

// ----------------- FUNCTIONS
require_once rex_path::core('functions/function_rex_globals.inc.php');
require_once rex_path::core('functions/function_rex_mquotes.inc.php');
require_once rex_path::core('functions/function_rex_other.inc.php');

// ----------------- VERSION
rex::setProperty('version', 5);
rex::setProperty('subversion', 0);
rex::setProperty('minorversion', 'alpha3');

$config = rex_file::getConfig(rex_path::data('config.yml'));
foreach ($config as $key => $value) {
  if (in_array($key, array('fileperm', 'dirperm'))) {
    $value = octdec($value);
  }
  rex::setProperty($key, $value);
}

date_default_timezone_set(rex::getProperty('timezone', 'Europe/Berlin'));

// ----------------- REX PERMS

rex_perm::register('advancedMode[]', rex_i18n::msg('perm_options_advancedMode[]'), rex_perm::OPTIONS);

rex_complex_perm::register('clang', 'rex_clang_perm');

// ----- SET CLANG
if (!rex::isSetup()) {
  rex_clang::setCurrentId(rex_request('clang', 'int', rex::getProperty('start_clang_id')));
}
