<?php
/**
 * Bindet nötige Klassen/Funktionen ein
 * @package redaxo4
 * @version svn:$Id$
 */

// ----------------- TIMER
require_once $REX['SRC_PATH']."/core/functions/function_rex_time.inc.php";

// ----------------- REDAXO requireS
// ----- FUNCTIONS
require_once $REX['SRC_PATH'].'/core/functions/function_rex_globals.inc.php';
require_once $REX['SRC_PATH'].'/core/functions/function_rex_ajax.inc.php';
require_once $REX['SRC_PATH'].'/core/functions/function_rex_client_cache.inc.php';
require_once $REX['SRC_PATH'].'/core/functions/function_rex_url.inc.php';
require_once $REX['SRC_PATH'].'/core/functions/function_rex_extension.inc.php';
require_once $REX['SRC_PATH'].'/core/functions/function_rex_addons.inc.php';
require_once $REX['SRC_PATH'].'/core/functions/function_rex_plugins.inc.php';
require_once $REX['SRC_PATH'].'/core/functions/function_rex_other.inc.php';

// ----- CLASSES
require_once $REX["SRC_PATH"].'/core/lib/autoload.php';

// add core base-classpath to autoloader
$loader = rex_autoload::getInstance($REX['SRC_PATH'] .'/generated/files/autoload.cache');
$loader->addDirectory($REX['SRC_PATH'] .'/core/lib/');
// register core-classes  as php-handlers
rex_autoload::register();
rex_logger::register();
// add core base-fragmentpath to fragmentloader
rex_fragment::addDirectory($REX['SRC_PATH'].'/core/fragments/');

//require_once $REX['SRC_PATH'].'/core/classes/class.i18n.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_sql.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_select.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_article_base.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_article.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_article_editor.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_template.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_login.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_addon.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_navigation.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_manager.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.ooredaxo.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.oocategory.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.ooarticle.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.ooarticleslice.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.oomediacategory.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.oomedia.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.ooaddon.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.ooplugin.inc.php';

if ($REX['REDAXO'])
{
  require_once $REX['SRC_PATH'].'/core/functions/function_rex_title.inc.php';
  require_once $REX['SRC_PATH'].'/core/functions/function_rex_generate.inc.php';
//  require_once $REX['SRC_PATH'].'/core/functions/function_rex_mediapool.inc.php';
//  require_once $REX['SRC_PATH'].'/core/functions/function_rex_structure.inc.php';
//  require_once $REX['SRC_PATH'].'/core/classes/class.rex_formatter.inc.php';
}

//require_once $REX['SRC_PATH'].'/core/classes/class.rex_form.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_list.inc.php';
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_select.inc.php';

// ----- EXTRA CLASSES
// require_once $REX['SRC_PATH'].'/config/classes/class.compat.inc.php';