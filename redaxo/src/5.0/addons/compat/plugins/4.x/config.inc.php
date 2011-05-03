<?php

global $I18N, $REX_USER, $REX_LOGIN, $article_id, $clang;

/**
 * @deprecated 5.0
 */
$REX['INCLUDE_PATH'] = rex_path::src();

/**
 * @deprecated 5.0
 */
$REX['FRONTEND_PATH'] = rex_path::frontend();

/**
 * @deprecated 5.0
 */
$REX['MEDIAFOLDER']   = rex_path::media();

/**
 * @deprecated 5.0
 */
$REX['FRONTEND_FILE'] = 'index.php';

if($REX['REDAXO'])
{
  /**
	 * @deprecated 5.0
	 */
  $I18N = new i18n($REX['LANG']);
}

/**
 * @deprecated 4.2
 */
$REX_USER =& $REX["USER"];

/**
 * @deprecated 4.2
 */
$REX_LOGIN = &$REX["LOGIN"];

/**
 * @deprecated 4.2
 */
$article_id =& $REX['ARTICLE_ID'];

/**
 * @deprecated 4.2
 */
$clang =& $REX['CUR_CLANG'];

$dir = dirname(__FILE__);
require_once $dir .'/functions/function_rex_client_cache.inc.php';
require_once $dir .'/functions/function_rex_extension.inc.php';
require_once $dir .'/functions/function_rex_file.inc.php';
require_once $dir .'/functions/function_rex_lang.inc.php';
require_once $dir .'/functions/function_rex_mediapool.inc.php';
require_once $dir .'/functions/function_rex_other.inc.php';

rex_addonManager::setClass('rex_addonManagerCompat');
rex_pluginManager::setClass('rex_pluginManagerCompat');