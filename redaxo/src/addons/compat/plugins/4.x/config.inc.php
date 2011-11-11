<?php

/**
 * @deprecated 5.0
 */

global $REX, $I18N;

$REX = new rex_compat_array;

$REX['HTDOCS_PATH']   = rex_path::frontend();
$REX['INCLUDE_PATH']  = rex_path::src();
$REX['FRONTEND_PATH'] = rex_path::frontend('', rex_path::ABSOLUTE);
$REX['MEDIAFOLDER']   = rex_path::media('', rex_path::ABSOLUTE);
$REX['FRONTEND_FILE'] = 'index.php';

$REX['GG'] = !rex::isBackend();

$REX->setCallbackAlias('CUR_CLANG', 'rex_clang::getId', 'rex_clang::setId');
$REX->setCallbackAlias('CLANG', 'rex_clang::getAll', 'rex_clang::reset');

$REX['PERM'] = new rex_perm_compat(rex_perm::GENERAL);
$REX['EXTPERM'] = new rex_perm_compat(rex_perm::OPTIONS);
$REX['EXTRAPERM'] = new rex_perm_compat(rex_perm::EXTRAS);

$REX['MOD_REWRITE'] = true;

if(rex::isBackend())
{
  $I18N = new i18n(rex::getProperty('lang'));
}


/**
 * @deprecated 4.2
 */

global $REX_USER, $REX_LOGIN, $article_id, $clang;

$REX_USER = rex::getUser();
$REX_LOGIN = rex::getProperty('login');

$REX->setGlobalVarAlias('ARTICLE_ID', 'article_id');
$REX->setGlobalVarAlias('CUR_CLANG', 'clang');


require_once __DIR__ .'/functions/function_rex_client_cache.inc.php';
require_once __DIR__ .'/functions/function_rex_extension.inc.php';
require_once __DIR__ .'/functions/function_rex_file.inc.php';
require_once __DIR__ .'/functions/function_rex_lang.inc.php';
require_once __DIR__ .'/functions/function_rex_mediapool.inc.php';
require_once __DIR__ .'/functions/function_rex_other.inc.php';
require_once __DIR__ .'/functions/function_rex_content.inc.php';

rex_addon_manager::setFactoryClass('rex_addon_manager_compat');
rex_plugin_manager::setFactoryClass('rex_plugin_manager_compat');