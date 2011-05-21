<?php

/**
 * Hauptkonfigurationsdatei
 * @package redaxo4
 * @version svn:$Id$
 */

require_once dirname(__FILE__) .'/lib/path.php';
rex_path::init($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER'], $REX['VERSION_FOLDER']);

require_once rex_path::core('lib/autoload.php');

// register core-classes  as php-handlers
rex_autoload::register();
// add core base-classpath to autoloader
rex_autoload::addDirectory(rex_path::core('lib/'));
// register rex_logger
rex_logger::register();
// start timer
rex::setProperty('timer', new rex_timer);
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
require_once rex_path::core('functions/function_rex_ajax.inc.php');
require_once rex_path::core('functions/function_rex_callable.inc.php');
require_once rex_path::core('functions/function_rex_other.inc.php');
require_once rex_path::core('functions/function_rex_generate.inc.php');

if(rex::isBackend())
{
  require_once rex_path::core('functions/function_rex_title.inc.php');
}

// ----------------- VERSION
rex::setProperty('version', 5);
rex::setProperty('subversion', 0);
rex::setProperty('minorversion', 0);

$config = rex_file::getConfig(rex_path::src('config.yml'));
foreach($config as $key => $value)
{
  rex::setProperty($key, $value);
}

date_default_timezone_set(rex::getProperty('timezone', 'Europe/Berlin'));

// ----------------- OTHER STUFF
rex::setProperty('setup_packages', array('be_style', 'be_style/agk_skin'));
rex::setProperty('system_packages', array('structure', 'structure/content', 'structure/linkmap', 'modules', 'templates', 'mediapool', 'import_export', 'metainfo', 'be_search', 'be_style', 'be_style/agk_skin', 'image_manager', 'users'));

// ----------------- REX PERMS

// ----- allgemein
$REX['PERM'] = array();

// ----- optionen
$REX['EXTPERM'] = array();
$REX['EXTPERM'][] = 'advancedMode[]';
$REX['EXTPERM'][] = 'accesskeys[]';
$REX['EXTPERM'][] = 'moveSlice[]';
$REX['EXTPERM'][] = 'moveArticle[]';
$REX['EXTPERM'][] = 'moveCategory[]';
$REX['EXTPERM'][] = 'copyArticle[]';
$REX['EXTPERM'][] = 'copyContent[]';
$REX['EXTPERM'][] = 'publishArticle[]';
$REX['EXTPERM'][] = 'publishCategory[]';
$REX['EXTPERM'][] = 'article2startpage[]';
$REX['EXTPERM'][] = 'article2category[]';

// ----- extras
$REX['EXTRAPERM'] = array();
$REX['EXTRAPERM'][] = 'editContentOnly[]';

// ----- SET CLANG
$REX['CLANG'] = array();
$clangFile = rex_path::cache('clang.cache');
if(file_exists($clangFile))
  $REX['CLANG'] = rex_file::getCache(rex_path::cache('clang.cache'));

$REX['CUR_CLANG']  = rex_request('clang','rex-clang-id', rex::getProperty('start_clang_id'));

if(rex_request('article_id', 'int') == 0)
  $REX['ARTICLE_ID'] = rex::getProperty('start_article_id');
else
  $REX['ARTICLE_ID'] = rex_request('article_id','rex-article-id', rex::getProperty('notfound_article_id'));

if(rex::isBackend())
{
  require rex_path::core('index_be.inc.php');
}
else
{
  require rex_path::core('index_fe.inc.php');
}