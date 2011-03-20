<?php
/**
 * Bindet nötige Klassen/Funktionen ein
 * @package redaxo4
 * @version svn:$Id$
 */

// ----------------- TIMER
require_once rex_path::src('core/functions/function_rex_time.inc.php');

// ----------------- REDAXO requireS
// ----- FUNCTIONS
require_once rex_path::src('core/functions/function_rex_globals.inc.php');
require_once rex_path::src('core/functions/function_rex_ajax.inc.php');
require_once rex_path::src('core/functions/function_rex_client_cache.inc.php');
require_once rex_path::src('core/functions/function_rex_url.inc.php');
require_once rex_path::src('core/functions/function_rex_extension.inc.php');
require_once rex_path::src('core/functions/function_rex_addons.inc.php');
require_once rex_path::src('core/functions/function_rex_other.inc.php');
require_once rex_path::src('core/functions/function_rex_generate.inc.php');

// ----- CLASSES
require_once rex_path::src('core/lib/autoload.php');

// add core base-classpath to autoloader
$loader = rex_autoload::getInstance(rex_path::generated('files/autoload.cache'));
$loader->addDirectory(rex_path::src('core/lib/'));
// register core-classes  as php-handlers
rex_autoload::register();
rex_logger::register();
// add core base-fragmentpath to fragmentloader
rex_fragment::addDirectory(rex_path::src('core/fragments/'));

if ($REX['REDAXO'])
{
  require_once rex_path::src('core/functions/function_rex_title.inc.php');
}