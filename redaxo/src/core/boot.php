<?php

use Symfony\Component\HttpFoundation\Request;

/**
 * REDAXO main boot file.
 *
 * @var array{HTDOCS_PATH: non-empty-string, BACKEND_FOLDER: non-empty-string, REDAXO: bool, LOAD_PAGE?: bool, PATH_PROVIDER?: object, URL_PROVIDER?: object} $REX
 *          HTDOCS_PATH    [Required] Relative path to htdocs directory
 *          BACKEND_FOLDER [Required] Name of backend folder
 *          REDAXO         [Required] Backend/Frontend flag
 *          LOAD_PAGE      [Optional] Wether the front controller should be loaded or not. Default value is false.
 *          PATH_PROVIDER  [Optional] Custom path provider
 *          URL_PROVIDER   [Optional] Custom url provider
 */

define('REX_MIN_PHP_VERSION', '8.3');

if (version_compare(PHP_VERSION, REX_MIN_PHP_VERSION) < 0) {
    echo 'Ooops, something went wrong!<br>';
    throw new Exception('PHP version >=' . REX_MIN_PHP_VERSION . ' needed!');
}

foreach (['HTDOCS_PATH', 'BACKEND_FOLDER', 'REDAXO'] as $key) {
    if (!isset($REX[$key])) {
        throw new Exception('Missing required global variable $REX[\'' . $key . "']");
    }
}

// start output buffering as early as possible, so we can be sure
// we can set http header whenever we want/need to
ob_start();
ob_implicit_flush(false);

if ('cli' !== PHP_SAPI) {
    // deactivate session cache limiter
    @session_cache_limiter('');
}

ini_set('session.use_strict_mode', '1');

ini_set('arg_separator.output', '&');
// disable html_errors to avoid html in exceptions and log files
if (ini_get('html_errors')) {
    ini_set('html_errors', '0');
}

require_once __DIR__ . '/lib/util/path.php';

if (isset($REX['PATH_PROVIDER']) && is_object($REX['PATH_PROVIDER'])) {
    /** @var rex_path_default_provider */
    $pathProvider = $REX['PATH_PROVIDER'];
} else {
    require_once __DIR__ . '/lib/util/path_default_provider.php';
    $pathProvider = new rex_path_default_provider($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER'], true);
}

rex_path::init($pathProvider);

require_once rex_path::core('lib/autoload.php');

// register core-classes as php-handlers
rex_autoload::register();
// add core base-classpath to autoloader
rex_autoload::addDirectory(rex_path::core('lib'));

// must be called after `rex_autoload::register()` to support symfony/polyfill-mbstring
mb_internal_encoding('UTF-8');

if (isset($REX['URL_PROVIDER']) && is_object($REX['URL_PROVIDER'])) {
    /** @var rex_path_default_provider */
    $urlProvider = $REX['URL_PROVIDER'];
} else {
    $urlProvider = new rex_path_default_provider($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER'], false);
}

rex_url::init($urlProvider);

// start timer at the very beginning
rex::setProperty('timer', new rex_timer($_SERVER['REQUEST_TIME_FLOAT'] ?? null));
// add backend flag to rex
rex::setProperty('redaxo', $REX['REDAXO']);
// add core lang directory to rex_i18n
rex_i18n::addDirectory(rex_path::core('lang'));
// add core base-fragmentpath to fragmentloader
rex_fragment::addDirectory(rex_path::core('fragments/'));

// ----------------- FUNCTIONS
require_once rex_path::core('functions/function_rex_escape.php');
require_once rex_path::core('functions/function_rex_globals.php');
require_once rex_path::core('functions/function_rex_mediapool.php');
require_once rex_path::core('functions/function_rex_other.php');

// ----------------- VERSION
rex::setProperty('version', '6.0.0-dev');

$cacheFile = rex_path::coreCache('config.yml.cache');
$configFile = rex_path::coreData('config.yml');

$cacheMtime = @filemtime($cacheFile);
if ($cacheMtime && $cacheMtime >= @filemtime($configFile)) {
    $config = rex_file::getCache($cacheFile);
} else {
    $config = array_merge(
        rex_file::getConfig(rex_path::core('default.config.yml')),
        rex_file::getConfig($configFile),
    );
    rex_file::putCache($cacheFile, $config);
}
/**
 * @var string $key
 * @var mixed $value
 */
foreach ($config as $key => $value) {
    if (in_array($key, ['fileperm', 'dirperm'])) {
        $value = octdec((string) $value);
    }
    rex::setProperty($key, $value);
}

date_default_timezone_set(rex::getProperty('timezone', 'Europe/Berlin'));

if ('cli' !== PHP_SAPI) {
    rex::setProperty('request', Request::createFromGlobals());
}

rex_error_handler::register();
rex_var_dumper::register();

// ----------------- REX PERMS

rex_user::setRoleClass(rex_user_role::class);

rex_complex_perm::register('clang', rex_clang_perm::class);
rex_complex_perm::register('structure', rex_structure_perm::class);
rex_complex_perm::register('modules', rex_module_perm::class);
rex_complex_perm::register('media', rex_media_perm::class);

rex_extension::register('COMPLEX_PERM_REMOVE_ITEM', [rex_user_role::class, 'removeOrReplaceItem']);
rex_extension::register('COMPLEX_PERM_REPLACE_ITEM', [rex_user_role::class, 'removeOrReplaceItem']);

// ----- SET CLANG
if (!rex::isSetup()) {
    $clangId = rex_request('clang', 'int', rex_clang::getStartId());
    if (rex::isBackend() || rex_clang::exists($clangId)) {
        rex_clang::setCurrentId($clangId);
    }
}

// ----------------- HTTPS REDIRECT
if ('cli' !== PHP_SAPI && !rex::isSetup()) {
    if ((true === rex::getProperty('use_https') || rex::getEnvironment() === rex::getProperty('use_https')) && !rex_request::isHttps()) {
        rex_response::enforceHttps();
    }

    if (true === rex::getProperty('use_hsts') && rex_request::isHttps()) {
        rex_response::setHeader('Strict-Transport-Security', 'max-age=' . (int) rex::getProperty('hsts_max_age', 31536000)); // default 1 year
    }
}

rex_extension::register('SESSION_REGENERATED', [rex_backend_login::class, 'sessionRegenerated']);

$nexttime = rex::isSetup() || rex::getConsole() ? 0 : (int) rex::getConfig('cronjob_nexttime', 0);
if (0 !== $nexttime && time() >= $nexttime) {
    $env = rex_cronjob_manager::getCurrentEnvironment();
    $EP = 'backend' === $env ? 'PAGE_CHECKED' : 'PACKAGES_INCLUDED';
    rex_extension::register($EP, static function () use ($env) {
        if ('backend' !== $env || !in_array(rex_be_controller::getCurrentPagePart(1), ['setup', 'login', 'cronjob'], true)) {
            rex_cronjob_manager_sql::factory()->check();
        }
    });
}

rex_extension::register('PACKAGES_INCLUDED', [rex_media_manager::class, 'init'], rex_extension::EARLY);
rex_extension::register('MEDIA_UPDATED', [rex_media_manager::class, 'mediaUpdated']);
rex_extension::register('MEDIA_DELETED', [rex_media_manager::class, 'mediaUpdated']);
rex_extension::register('MEDIA_IS_IN_USE', [rex_media_manager::class, 'mediaIsInUse']);

if (!rex::isSetup()) {
    require_once __DIR__ . '/functions/function_structure_rex_url.php';

    rex::setProperty('start_article_id', rex::getConfig('start_article_id', 1));
    rex::setProperty('notfound_article_id', rex::getConfig('notfound_article_id', 1));
    rex::setProperty('rows_per_page', 50);

    if (0 == rex_request('article_id', 'int')) {
        rex::setProperty('article_id', rex_article::getSiteStartArticleId());
    } else {
        $articleId = rex_request('article_id', 'int');
        $articleId = rex_article::get($articleId) ? $articleId : rex_article::getNotfoundArticleId();
        rex::setProperty('article_id', $articleId);
    }

    rex_extension::register('EDITOR_URL', static function (rex_extension_point $ep) {
        $urls = [
            'template' => ['templates', 'template_id'],
            'module' => ['modules/modules', 'module_id'],
            'action' => ['modules/actions', 'action_id'],
        ];

        if (preg_match('@^rex:///(template|module|action)/(\d+)@', $ep->getParam('file'), $match)) {
            return rex_url::backendPage($urls[$match[1]][0], ['function' => 'edit', $urls[$match[1]][1] => $match[2]]);
        }

        return null;
    });

    rex_extension::register('EDITOR_URL', static function (rex_extension_point $ep) {
        if (!preg_match('@^rex:///metainfo/(\d+)@', $ep->getParam('file'), $match)) {
            return null;
        }

        $id = $match[1];
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT `name` FROM ' . rex::getTable('metainfo_field') . ' WHERE id = ? LIMIT 1', [$id]);

        if (!$sql->getRows()) {
            return null;
        }

        $prefix = rex_metainfo_meta_prefix((string) $sql->getValue('name'));
        $page = match ($prefix) {
            'art_' => 'articles',
            'cat_' => 'categories',
            'med_' => 'media',
            'clang_' => 'clangs',
            default => throw new LogicException('Unknown metainfo prefix "' . $prefix . '"'),
        };

        return rex_url::backendPage('metainfo/' . $page, ['func' => 'edit', 'field_id' => $id]);
    });
}

if (isset($REX['LOAD_PAGE']) && $REX['LOAD_PAGE']) {
    unset($REX);
    require rex_path::core(rex::isBackend() ? 'backend.php' : 'frontend.php');
}
