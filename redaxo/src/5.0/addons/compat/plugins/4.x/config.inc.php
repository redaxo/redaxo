<?php

global $I18N, $REX_USER, $REX_LOGIN, $article_id, $clang;

/**
 * @deprecated 5.0
 */
$REX['INCLUDE_PATH'] = rex_path::version();

/**
 * @deprecated 5.0
 */
$REX['FRONTEND_PATH'] = rex_path::frontend();

/**
 * @deprecated 5.0
 */
$REX['MEDIAFOLDER']   = rex_path::media('', rex_path::ABSOLUTE);

/**
 * @deprecated 5.0
 */
$REX['FRONTEND_FILE'] = 'index.php';

if(rex_core::isBackend())
{
  /**
	 * @deprecated 5.0
	 */
  $I18N = new i18n(rex_core::getProperty('lang'));
}

/**
 * @deprecated 4.2
 */
$REX_USER = rex_core::getUser();

/**
 * @deprecated 4.2
 */
$REX_LOGIN = rex_core::getProperty('login');

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

rex_addonManager::setFactoryClass('rex_addonManagerCompat');
rex_pluginManager::setFactoryClass('rex_pluginManagerCompat');