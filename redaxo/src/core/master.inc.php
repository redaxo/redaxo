<?php

/**
 * Hauptkonfigurationsdatei
 * @package redaxo4
 */

if(version_compare(PHP_VERSION, '5.3.0') < 0)
{
  exit('PHP version >=5.3 needed!');
}

mb_internal_encoding('UTF-8');

require_once dirname(__FILE__) .'/lib/path.php';
rex_path::init($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER']);

require_once rex_path::core('lib/autoload.php');

// register core-classes  as php-handlers
rex_autoload::register();
// add core base-classpath to autoloader
rex_autoload::addDirectory(rex_path::core('lib/'));
// start timer at the very beginning
rex::setProperty('timer', new rex_timer);
// register rex_logger
rex_logger::register();
// add backend flag to rex
rex::setProperty('redaxo', $REX['REDAXO']);
// reset $REX
unset($REX);
// add core lang directory to rex_i18n
rex_i18n::addDirectory(rex_path::core('lang'));
// add core base-fragmentpath to fragmentloader
rex_fragment::addDirectory(rex_path::core('fragments/'));
// register core REX_VARS
rex_var::registerVar('rex_var_config');

// ----------------- FUNCTIONS
require_once rex_path::core('functions/function_rex_globals.inc.php');
require_once rex_path::core('functions/function_rex_mquotes.inc.php');
require_once rex_path::core('functions/function_rex_other.inc.php');

// ----------------- VERSION
rex::setProperty('version', 5);
rex::setProperty('subversion', 0);
rex::setProperty('minorversion', 'alpha3');

$config = rex_file::getConfig(rex_path::data('config.yml'));
foreach($config as $key => $value)
{
  if(in_array($key, array('fileperm', 'dirperm')))
  {
    $value = octdec($value);
  }
  rex::setProperty($key, $value);
}

date_default_timezone_set(rex::getProperty('timezone', 'Europe/Berlin'));

// ----------------- REX PERMS

rex_perm::register('advancedMode[]', rex_i18n::msg('perm_options_advancedMode[]'), rex_perm::OPTIONS);
rex_perm::register('accesskeys[]', rex_i18n::msg('perm_options_accesskeys[]'), rex_perm::OPTIONS);

rex_complex_perm::register('clang', 'rex_clang_perm');

// ----- SET CLANG
if(!rex::isSetup())
{
  rex_clang::setId(rex_request('clang', 'int', rex::getProperty('start_clang_id')));
}

if(rex::isBackend())
{
  require rex_path::core('index_be.inc.php');
}
else
{
  require rex_path::core('index_fe.inc.php');
}
