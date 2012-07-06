<?php

/**
 * @deprecated 5.0
 */

global $REX, $I18N;

$REX = new rex_compat_array;

$REX['HTDOCS_PATH']   = rex_path::frontend();
$REX['INCLUDE_PATH']  = rtrim(rex_path::src(), DIRECTORY_SEPARATOR);
$REX['FRONTEND_PATH'] = rtrim(rex_path::frontend('', rex_path::ABSOLUTE), DIRECTORY_SEPARATOR);
$REX['MEDIAFOLDER']   = rtrim(rex_path::media('', rex_path::ABSOLUTE), DIRECTORY_SEPARATOR);
$REX['FRONTEND_FILE'] = 'index.php';

$REX['GG'] = !rex::isBackend();

$REX->setCallbackAlias('CUR_CLANG', 'rex_clang::getCurrentId', 'rex_clang::setCurrentId');
$clangGetAll = function () {
  return array_map(function (rex_clang $clang) {
    return $clang->getName();
  }, rex_clang::getAll());
};
$REX->setCallbackAlias('CLANG', $clangGetAll, 'rex_clang::reset');

$REX['PERM'] = new rex_perm_compat(rex_perm::GENERAL);
$REX['EXTPERM'] = new rex_perm_compat(rex_perm::OPTIONS);
$REX['EXTRAPERM'] = new rex_perm_compat(rex_perm::EXTRAS);

$REX['MOD_REWRITE'] = true;

if (rex::isBackend()) {
  $I18N = new i18n(rex::getProperty('lang'));
}

$deprecatedExtensionPoint = function ($oldEP, $newEP) {
  rex_extension::register($newEP, function ($params) use ($oldEP) {
    return rex_extension::registerPoint($oldEP, $params['subject'], $params);
  });
};
$deprecatedExtensionPoint('ALL_GENERATED', 'CACHE_DELETED');
$deprecatedExtensionPoint('OOMEDIA_IS_IN_USE', 'MEDIA_IS_IN_USE');


/**
 * @deprecated 4.2
 */

global $REX_USER, $REX_LOGIN, $article_id, $clang;

$REX_USER = rex::getUser();
$REX_LOGIN = rex::getProperty('login');

$REX->setGlobalVarAlias('ARTICLE_ID', 'article_id');
$REX->setGlobalVarAlias('CUR_CLANG', 'clang');


require_once __DIR__ . '/functions/function_rex_client_cache.inc.php';
require_once __DIR__ . '/functions/function_rex_extension.inc.php';
require_once __DIR__ . '/functions/function_rex_file.inc.php';
require_once __DIR__ . '/functions/function_rex_lang.inc.php';
require_once __DIR__ . '/functions/function_rex_mediapool.inc.php';
require_once __DIR__ . '/functions/function_rex_other.inc.php';
require_once __DIR__ . '/functions/function_rex_content.inc.php';

rex_addon_manager::setFactoryClass('rex_addon_manager_compat');
rex_plugin_manager::setFactoryClass('rex_plugin_manager_compat');
