<?php
/**
 * Bindet nötige Klassen/Funktionen ein
 * @package redaxo4
 * @version svn:$Id$
 */

// ----------------- TIMER
include_once $REX['INCLUDE_PATH']."/functions/function_rex_time.inc.php";

// ----------------- REDAXO INCLUDES
// ----- FUNCTIONS
include_once $REX['INCLUDE_PATH'].'/functions/function_rex_globals.inc.php';
include_once $REX['INCLUDE_PATH'].'/functions/function_rex_ajax.inc.php';
include_once $REX['INCLUDE_PATH'].'/functions/function_rex_client_cache.inc.php';
include_once $REX['INCLUDE_PATH'].'/functions/function_rex_url.inc.php';
include_once $REX['INCLUDE_PATH'].'/functions/function_rex_extension.inc.php';
include_once $REX['INCLUDE_PATH'].'/functions/function_rex_addons.inc.php';
include_once $REX['INCLUDE_PATH'].'/functions/function_rex_plugins.inc.php';
include_once $REX['INCLUDE_PATH'].'/functions/function_rex_other.inc.php';

// ----- CLASSES
include_once $REX['INCLUDE_PATH'].'/classes/class.i18n.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_sql.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_select.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_article_base.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_article.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_article_editor.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_template.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_login.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_addon.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_navigation.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_manager.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.ooredaxo.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.oocategory.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.ooarticle.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.ooarticleslice.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.oomediacategory.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.oomedia.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.ooaddon.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.ooplugin.inc.php';

if ($REX['REDAXO'])
{
  include_once $REX['INCLUDE_PATH'].'/functions/function_rex_title.inc.php';
  include_once $REX['INCLUDE_PATH'].'/functions/function_rex_generate.inc.php';
  include_once $REX['INCLUDE_PATH'].'/functions/function_rex_mediapool.inc.php';
  include_once $REX['INCLUDE_PATH'].'/functions/function_rex_structure.inc.php';
  include_once $REX['INCLUDE_PATH'].'/classes/class.rex_formatter.inc.php';
}

include_once $REX['INCLUDE_PATH'].'/classes/class.rex_form.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_list.inc.php';
include_once $REX['INCLUDE_PATH'].'/classes/class.rex_select.inc.php';

include_once $REX['INCLUDE_PATH'].'/classes/class.rex_var.inc.php';
foreach($REX['VARIABLES'] as $key => $value)
{
  require_once ($REX['INCLUDE_PATH'].'/classes/variables/class.'.$value.'.inc.php');
  $REX['VARIABLES'][$key] = new $value;
}

// ----- EXTRA CLASSES
// include_once $REX['INCLUDE_PATH'].'/classes/class.compat.inc.php';