<?php

global $REX, $I18N, $REX_USER, $REX_LOGIN, $article_id, $clang;

/**
 * @deprecated 5.0
 */
// TODO uncomment this when $REX is completely removed from core
//$REX = new rex_compat_array;

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

/**
 * @deprecated 5.0
 */
$REX['GG'] = !rex::isBackend();

if(rex::isBackend())
{
  /**
	 * @deprecated 5.0
	 */
  $I18N = new i18n(rex::getProperty('lang'));
}

/**
 * @deprecated 4.2
 */
$REX_USER = rex::getUser();

/**
 * @deprecated 4.2
 */
$REX_LOGIN = rex::getProperty('login');

/**
 * @deprecated 4.2
 */
$article_id =& $REX['ARTICLE_ID'];

/**
 * @deprecated 4.2
 */
$clang =& $REX['CUR_CLANG'];

require_once __DIR__ .'/functions/function_rex_client_cache.inc.php';
require_once __DIR__ .'/functions/function_rex_extension.inc.php';
require_once __DIR__ .'/functions/function_rex_file.inc.php';
require_once __DIR__ .'/functions/function_rex_lang.inc.php';
require_once __DIR__ .'/functions/function_rex_mediapool.inc.php';
require_once __DIR__ .'/functions/function_rex_other.inc.php';

rex_addon_manager::setFactoryClass('rex_addon_manager_compat');
rex_plugin_manager::setFactoryClass('rex_plugin_manager_compat');