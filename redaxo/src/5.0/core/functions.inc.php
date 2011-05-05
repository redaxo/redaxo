<?php
/**
 * Bindet nötige Klassen/Funktionen ein
 * @package redaxo5
 * @version svn:$Id$
 */

// ----------------- TIMER
require_once rex_path::core('lib/util/timer.php');
rex_timer::getInstance()->start();

// ----------------- REDAXO requireS
// ----- FUNCTIONS
require_once rex_path::core('functions/function_rex_globals.inc.php');
require_once rex_path::core('functions/function_rex_ajax.inc.php');
require_once rex_path::core('functions/function_rex_url.inc.php');
require_once rex_path::core('functions/function_rex_callable.inc.php');
require_once rex_path::core('functions/function_rex_other.inc.php');
require_once rex_path::core('functions/function_rex_generate.inc.php');

// ----- CLASSES
require_once rex_path::core('lib/autoload.php');

// add core base-classpath to autoloader
rex_autoload::addDirectory(rex_path::core('lib/'));
// register core-classes  as php-handlers
rex_autoload::register();
// register rex_logger
rex_logger::register();
// add core lang directory to rex_i18n
rex_i18n::addDirectory(rex_path::core('lang'));
// add core base-fragmentpath to fragmentloader
rex_fragment::addDirectory(rex_path::core('fragments/'));
// register core REX_VARS
rex_var::registerVar('rex_var_config');

if ($REX['REDAXO'])
{
  require_once rex_path::core('functions/function_rex_title.inc.php');
}