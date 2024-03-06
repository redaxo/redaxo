<?php

use Redaxo\Core\Core;
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

require_once rex_path::base('vendor/autoload.php');

// must be called after autoloader to support symfony/polyfill-mbstring
mb_internal_encoding('UTF-8');

if (isset($REX['URL_PROVIDER']) && is_object($REX['URL_PROVIDER'])) {
    /** @var rex_path_default_provider */
    $urlProvider = $REX['URL_PROVIDER'];
} else {
    $urlProvider = new rex_path_default_provider($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER'], false);
}

rex_url::init($urlProvider);

// start timer at the very beginning
Core::setProperty('timer', new rex_timer($_SERVER['REQUEST_TIME_FLOAT'] ?? null));
// add backend flag to rex
Core::setProperty('redaxo', $REX['REDAXO']);
// add core lang directory to rex_i18n
rex_i18n::addDirectory(rex_path::core('lang'));
// add core base-fragmentpath to fragmentloader
rex_fragment::addDirectory(rex_path::core('fragments/'));

// ----------------- VERSION
Core::setProperty('version', '6.0.0-dev');

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
    Core::setProperty($key, $value);
}

date_default_timezone_set(Core::getProperty('timezone', 'Europe/Berlin'));

if ('cli' !== PHP_SAPI) {
    Core::setProperty('request', Request::createFromGlobals());
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
if (!Core::isSetup()) {
    $clangId = rex_request('clang', 'int', rex_clang::getStartId());
    if (Core::isBackend() || rex_clang::exists($clangId)) {
        rex_clang::setCurrentId($clangId);
    }
}

// ----------------- HTTPS REDIRECT
if ('cli' !== PHP_SAPI && !Core::isSetup()) {
    if ((true === Core::getProperty('use_https') || Core::getEnvironment() === Core::getProperty('use_https')) && !rex_request::isHttps()) {
        rex_response::enforceHttps();
    }

    if (true === Core::getProperty('use_hsts') && rex_request::isHttps()) {
        rex_response::setHeader('Strict-Transport-Security', 'max-age=' . (int) Core::getProperty('hsts_max_age', 31536000)); // default 1 year
    }
}

rex_extension::register('SESSION_REGENERATED', rex_backend_login::sessionRegenerated(...));

$nexttime = Core::isSetup() || Core::getConsole() ? 0 : (int) Core::getConfig('cronjob_nexttime', 0);
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

if (!Core::isSetup()) {
    Core::setProperty('start_article_id', Core::getConfig('start_article_id', 1));
    Core::setProperty('notfound_article_id', Core::getConfig('notfound_article_id', 1));
    Core::setProperty('rows_per_page', 50);

    if (0 == rex_request('article_id', 'int')) {
        Core::setProperty('article_id', rex_article::getSiteStartArticleId());
    } else {
        $articleId = rex_request('article_id', 'int');
        $articleId = rex_article::get($articleId) ? $articleId : rex_article::getNotfoundArticleId();
        Core::setProperty('article_id', $articleId);
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
}

if (isset($REX['LOAD_PAGE']) && $REX['LOAD_PAGE']) {
    unset($REX);
    require rex_path::core(Core::isBackend() ? 'backend.php' : 'frontend.php');
}
