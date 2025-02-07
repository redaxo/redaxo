<?php

use Redaxo\Core\Backend\Controller;
use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\ModulePermission;
use Redaxo\Core\Content\StructurePermission;
use Redaxo\Core\Core;
use Redaxo\Core\Cronjob\CronjobExecutor;
use Redaxo\Core\Cronjob\CronjobManager;
use Redaxo\Core\ErrorHandler;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Filesystem\DefaultPathProvider;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Http\Response;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Language\LanguagePermission;
use Redaxo\Core\MediaManager\MediaManager;
use Redaxo\Core\MediaPool\MediaPoolPermission;
use Redaxo\Core\Security\BackendLogin;
use Redaxo\Core\Security\ComplexPermission;
use Redaxo\Core\Security\User;
use Redaxo\Core\Security\UserRole;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Timer;
use Redaxo\Core\Util\VarDumper;
use Redaxo\Core\View\Fragment;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

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

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

if (isset($REX['PATH_PROVIDER']) && is_object($REX['PATH_PROVIDER'])) {
    /** @var DefaultPathProvider */
    $pathProvider = $REX['PATH_PROVIDER'];
} else {
    $pathProvider = new DefaultPathProvider($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER'], true);
}

Path::init($pathProvider);

// must be called after autoloader to support symfony/polyfill-mbstring
mb_internal_encoding('UTF-8');

if (isset($REX['URL_PROVIDER']) && is_object($REX['URL_PROVIDER'])) {
    /** @var DefaultPathProvider */
    $urlProvider = $REX['URL_PROVIDER'];
} else {
    $urlProvider = new DefaultPathProvider($REX['HTDOCS_PATH'], $REX['BACKEND_FOLDER'], false);
}

Url::init($urlProvider);

// start timer at the very beginning
Core::setProperty('timer', new Timer($_SERVER['REQUEST_TIME_FLOAT'] ?? null));
// add backend flag to rex
Core::setProperty('redaxo', $REX['REDAXO']);
// add core lang directory to I18n
I18n::addDirectory(Path::core('lang'));
// add core base-fragmentpath to fragmentloader
Fragment::addDirectory(Path::core('fragments/'));

// ----------------- VERSION
Core::setProperty('version', '6.0.0-dev');

$cacheFile = Path::coreCache('config.yml.cache');
$configFile = Path::coreData('config.yml');

$cacheMtime = @filemtime($cacheFile);
if ($cacheMtime && $cacheMtime >= @filemtime($configFile)) {
    $config = File::getCache($cacheFile);
} else {
    $config = array_merge(
        File::getConfig(Path::core('default.config.yml')),
        File::getConfig($configFile),
    );
    File::putCache($cacheFile, $config);
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
    Core::setProperty('request', BaseRequest::createFromGlobals());
}

ErrorHandler::register();
VarDumper::register();

// ----------------- REX PERMS

User::setRoleClass(UserRole::class);

ComplexPermission::register('clang', LanguagePermission::class);
ComplexPermission::register('structure', StructurePermission::class);
ComplexPermission::register('modules', ModulePermission::class);
ComplexPermission::register('media', MediaPoolPermission::class);

Extension::register('COMPLEX_PERM_REMOVE_ITEM', [UserRole::class, 'removeOrReplaceItem']);
Extension::register('COMPLEX_PERM_REPLACE_ITEM', [UserRole::class, 'removeOrReplaceItem']);

// ----- SET CLANG
if (!Core::isSetup()) {
    $clangId = Request::request('clang', 'int', Language::getStartId());
    if (Core::isBackend() || Language::exists($clangId)) {
        Language::setCurrentId($clangId);
    }
}

// ----------------- HTTPS REDIRECT
if ('cli' !== PHP_SAPI && !Core::isSetup()) {
    if ((true === Core::getProperty('use_https') || Core::getEnvironment() === Core::getProperty('use_https')) && !Request::isHttps()) {
        Response::enforceHttps();
    }

    if (true === Core::getProperty('use_hsts') && Request::isHttps()) {
        Response::setHeader('Strict-Transport-Security', 'max-age=' . (int) Core::getProperty('hsts_max_age', 31536000)); // default 1 year
    }
}

Extension::register('SESSION_REGENERATED', BackendLogin::sessionRegenerated(...));

$nexttime = Core::isSetup() || Core::getConsole() ? 0 : (int) Core::getConfig('cronjob_nexttime', 0);
if (0 !== $nexttime && time() >= $nexttime) {
    $env = CronjobExecutor::getCurrentEnvironment();
    $EP = 'backend' === $env ? 'PAGE_CHECKED' : 'PACKAGES_INCLUDED';
    Extension::register($EP, static function () use ($env) {
        if ('backend' !== $env || !in_array(Controller::getCurrentPagePart(1), ['setup', 'login', 'cronjob'], true)) {
            CronjobManager::factory()->check();
        }
    });
}

Extension::register('PACKAGES_INCLUDED', [MediaManager::class, 'init'], Extension::EARLY);
Extension::register('MEDIA_UPDATED', [MediaManager::class, 'mediaUpdated']);
Extension::register('MEDIA_DELETED', [MediaManager::class, 'mediaUpdated']);
Extension::register('MEDIA_IS_IN_USE', [MediaManager::class, 'mediaIsInUse']);

if (!Core::isSetup()) {
    Core::setProperty('start_article_id', Core::getConfig('start_article_id', 1));
    Core::setProperty('notfound_article_id', Core::getConfig('notfound_article_id', 1));
    Core::setProperty('rows_per_page', 50);

    if (0 == Request::request('article_id', 'int')) {
        Core::setProperty('article_id', Article::getSiteStartArticleId());
    } else {
        $articleId = Request::request('article_id', 'int');
        $articleId = Article::get($articleId) ? $articleId : Article::getNotfoundArticleId();
        Core::setProperty('article_id', $articleId);
    }

    Extension::register('EDITOR_URL', static function (ExtensionPoint $ep) {
        $urls = [
            'template' => ['templates', 'template_id'],
            'module' => ['modules/modules', 'module_id'],
            'action' => ['modules/actions', 'action_id'],
        ];

        if (preg_match('@^rex:///(template|module|action)/(\d+)@', $ep->getParam('file'), $match)) {
            return Url::backendPage($urls[$match[1]][0], ['function' => 'edit', $urls[$match[1]][1] => $match[2]]);
        }

        return null;
    });
}

if (isset($REX['LOAD_PAGE']) && $REX['LOAD_PAGE']) {
    unset($REX);
    require Path::core(Core::isBackend() ? 'backend.php' : 'frontend.php');
}
