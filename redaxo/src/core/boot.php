<?php

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
    rex::setProperty('request', Symfony\Component\HttpFoundation\Request::createFromGlobals());
}

rex_error_handler::register();
rex_var_dumper::register();

// ----------------- REX PERMS

rex_user::setRoleClass(rex_user_role::class);
rex_complex_perm::register('clang', rex_clang_perm::class);

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

if (!rex::isSetup()) {
    rex_perm::register('addArticle[]', null, rex_perm::OPTIONS);
    rex_perm::register('addCategory[]', null, rex_perm::OPTIONS);
    rex_perm::register('editArticle[]', null, rex_perm::OPTIONS);
    rex_perm::register('editCategory[]', null, rex_perm::OPTIONS);
    rex_perm::register('deleteArticle[]', null, rex_perm::OPTIONS);
    rex_perm::register('deleteCategory[]', null, rex_perm::OPTIONS);
    rex_perm::register('moveArticle[]', null, rex_perm::OPTIONS);
    rex_perm::register('moveCategory[]', null, rex_perm::OPTIONS);
    rex_perm::register('copyArticle[]', null, rex_perm::OPTIONS);
    rex_perm::register('copyContent[]', null, rex_perm::OPTIONS);
    rex_perm::register('publishArticle[]', null, rex_perm::OPTIONS);
    rex_perm::register('publishCategory[]', null, rex_perm::OPTIONS);
    rex_perm::register('article2startarticle[]', null, rex_perm::OPTIONS);
    rex_perm::register('article2category[]', null, rex_perm::OPTIONS);

    rex_complex_perm::register('structure', rex_structure_perm::class);

    require_once __DIR__.'/functions/function_structure_rex_url.php';

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

    /**
     * History.
     */
    if (true === rex::getConfig('history', false)) {
        rex_extension::register('PAGE_CHECKED', static function (rex_extension_point $ep) {
            $page = rex_be_controller::getPageObject('content');
            if ($page && $historyPage = $page->getSubpage('history')) {
                $historyPage->setHidden(false);
            }
            $page = rex_be_controller::getPageObject('system');
            if ($page && $historyPage = $page->getSubpage('history')) {
                $historyPage->setHidden(false);
            }
        });
        $historyDate = rex_request('rex_history_date', 'string');

        rex_perm::register('history[article_rollback]', null, rex_perm::OPTIONS);

        if ('' != $historyDate) {
            $historySession = rex_request('rex_history_session', 'string');
            $historyLogin = rex_request('rex_history_login', 'string');
            $historyValidtime = rex_request('rex_history_validtime', 'string');

            $user = null;
            if ('' != $historySession && '' != $historyLogin && '' != $historyValidtime && !rex::isBackend()) {
                $validtill = DateTime::createFromFormat('YmdHis', $historyValidtime);
                $now = new DateTime();
                if ($now < $validtill) {
                    $login = new rex_history_login();

                    if ($login->checkTempSession($historyLogin, $historySession, $historyValidtime)) {
                        $user = $login->getUser();
                        rex::setProperty('user', $user);
                        rex_extension::register(
                            'OUTPUT_FILTER',
                            static function (rex_extension_point $ep) use ($login) {
                                $login->deleteSession();
                            }
                        );
                    }
                }
            } else {
                $user = rex_backend_login::createUser();
            }

            if (!$user) {
                throw new rex_exception('no permission');
            }

            if (!$user->hasPerm('history[article_rollback]')) {
                throw new rex_exception('no permission for the slice version');
            }

            rex_extension::register('ART_INIT', static function (rex_extension_point $ep) {
                $article = $ep->getParam('article');
                if ($article instanceof rex_article_content) {
                    $article->getContentAsQuery();
                }
                $article->setEval(true);
            });

            rex_extension::register('ART_SLICES_QUERY', static function (rex_extension_point $ep) {
                $historyDate = rex_request('rex_history_date', 'string');
                $article = $ep->getParam('article');

                if ($article instanceof rex_article_content && $article->getArticleId() == rex_article::getCurrentId(
                    )) {
                    $articleLimit = '';
                    if (0 != $article->getArticleId()) {
                        $articleLimit = ' AND '.rex::getTablePrefix(
                            ).'article_slice.article_id='.$article->getArticleId();
                    }

                    $sliceLimit = '';

                    rex_article_slice_history::checkTables();

                    $escapeSql = rex_sql::factory();

                    $sliceDate = ' AND '.rex::getTablePrefix().'article_slice.history_date = '.$escapeSql->escape(
                            $historyDate
                        );

                    return 'SELECT '.rex::getTablePrefix().'module.id, '.rex::getTablePrefix(
                        ).'module.key,'.rex::getTablePrefix().'module.name, '.rex::getTablePrefix(
                        ).'module.output, '.rex::getTablePrefix().'module.input, '.rex::getTablePrefix(
                        ).'article_slice.*, '.rex::getTablePrefix().'article.parent_id
                        FROM
                            '.rex_article_slice_history::getTable().' as '.rex::getTablePrefix().'article_slice
                        LEFT JOIN '.rex::getTablePrefix().'module ON '.rex::getTablePrefix(
                        ).'article_slice.module_id='.rex::getTablePrefix().'module.id
                        LEFT JOIN '.rex::getTablePrefix().'article ON '.rex::getTablePrefix(
                        ).'article_slice.article_id='.rex::getTablePrefix().'article.id
                        WHERE
                            '.rex::getTablePrefix()."article_slice.clang_id='".$article->getClangId()."' AND
                            ".rex::getTablePrefix()."article.clang_id='".$article->getClangId()."' AND
                            ".rex::getTablePrefix().'article_slice.revision=0
                            '.$articleLimit.'
                            '.$sliceLimit.'
                            '.$sliceDate.'
                            ORDER BY '.rex::getTablePrefix().'article_slice.priority';
                }

                return null;
            });
        }

        rex_cronjob_manager::registerType(rex_cronjob_structure_history::class);
    }

    // Version extension
    if (true === rex::getConfig('structure_version', false)) {
        rex_perm::register('version[live_version]', null, rex_perm::OPTIONS);

        // ***** an EPs andocken
        rex_extension::register('ART_INIT', static function (rex_extension_point $ep) {
            $version = rex_request('rex_version', 'int');
            if (rex_article_revision::WORK != $version) {
                return;
            }

            if (!rex_backend_login::hasSession()) {
                $fragment = new rex_fragment([
                    'content' => '<p>No permission for the working version. You need to be logged into the REDAXO backend at the same time.</p>',
                ]);
                rex_response::setStatus(rex_response::HTTP_UNAUTHORIZED);
                rex_response::sendPage($fragment->parse('core/fe_ooops.php'));
                exit;
            }

            /** @var rex_article_content_base $article */
            $article = $ep->getParam('article');
            $article->setSliceRevision($version);
            if ($article instanceof rex_article_content) {
                $article->getContentAsQuery();
            }
            $article->setEval(true);
        });

        rex_extension::register('STRUCTURE_CONTENT_HEADER', static function (rex_extension_point $ep) {
            if ('content/edit' !== $ep->getParam('page')) {
                return null;
            }

            $params = $ep->getParams();
            $articleId = rex_type::int($params['article_id']);

            $version = rex_article_revision::getSessionArticleRevision($articleId);
            $newVersion = rex_request('rex_set_version', 'int', null);

            if (rex_article_revision::LIVE === $newVersion) {
                $version = rex_article_revision::LIVE;
            } elseif (rex_article_revision::WORK === $newVersion) {
                $version = rex_article_revision::WORK;
            }

            if (!rex::requireUser()->hasPerm('version[live_version]')) {
                $version = rex_article_revision::WORK;
            }

            rex_article_revision::setSessionArticleRevision($articleId, $version);

            $params['slice_revision'] = $version;
        });

        rex_extension::register('STRUCTURE_CONTENT_BEFORE_SLICES', static function (rex_extension_point $ep) {
            if ('content/edit' !== $ep->getParam('page')) {
                return null;
            }

            $user = rex::requireUser();
            $params = $ep->getParams();
            $articleId = rex_type::int($params['article_id']);
            $clangId = rex_type::int($params['clang']);
            $return = rex_type::string($ep->getSubject());

            $workingVersionEmpty = true;
            $gw = rex_sql::factory();
            $gw->setQuery(
                'select * from '.rex::getTablePrefix(
                ).'article_slice where article_id=? and clang_id=? and revision=1 LIMIT 1',
                [$articleId, $clangId]
            );
            if ($gw->getRows() > 0) {
                $workingVersionEmpty = false;
            }

            $func = rex_request('rex_version_func', 'string');
            switch ($func) {
                case 'copy_work_to_live':
                    if ($workingVersionEmpty) {
                        $return .= rex_view::error(rex_i18n::msg('version_warning_working_version_to_live'));
                    } elseif ($user->hasPerm('version[live_version]')) {
                        if (true === rex::getConfig('history', false)) {
                            rex_article_slice_history::makeSnapshot($articleId, $clangId, 'work_to_live');
                        }

                        rex_article_revision::copyContent(
                            $articleId,
                            $clangId,
                            rex_article_revision::WORK,
                            rex_article_revision::LIVE
                        );
                        $return .= rex_view::success(rex_i18n::msg('version_info_working_version_to_live'));

                        $article = rex_type::instanceOf(rex_article::get($articleId, $clangId), rex_article::class);
                        rex_article_revision::setSessionArticleRevision($articleId, rex_article_revision::LIVE);
                        $return = rex_extension::registerPoint(
                            new rex_extension_point_art_content_updated($article, 'work_to_live', $return),
                        );
                    }
                    break;
                case 'copy_live_to_work':
                    rex_article_revision::copyContent(
                        $articleId,
                        $clangId,
                        rex_article_revision::LIVE,
                        rex_article_revision::WORK,
                    );
                    $return .= rex_view::success(rex_i18n::msg('version_info_live_version_to_working'));
                    rex_article_revision::setSessionArticleRevision($articleId, rex_article_revision::WORK);
                    break;
                case 'clear_work':
                    rex_article_revision::clearContent($articleId, $clangId, rex_article_revision::WORK);
                    $return .= rex_view::success(rex_i18n::msg('version_info_clear_workingversion'));
                    break;
            }

            $revision = rex_article_revision::getSessionArticleRevision($articleId);

            $revisions = [];
            if ($user->hasPerm('version[live_version]')) {
                $revisions[rex_article_revision::LIVE] = rex_i18n::msg('version_liveversion');
            }
            $revisions[rex_article_revision::WORK] = rex_i18n::msg('version_workingversion');

            $context = new rex_context([
                'page' => $params['page'],
                'article_id' => $articleId,
                'clang' => $clangId,
                'ctype' => $params['ctype'],
            ]);

            $items = [];
            $currentRevision = '';
            foreach ($revisions as $version => $label) {
                $item = [];
                $item['title'] = $label;
                $item['href'] = $context->getUrl(['rex_set_version' => $version]);
                if ($revision == $version) {
                    $item['active'] = true;
                    $currentRevision = $label;
                }
                $items[] = $item;
            }

            $toolbar = '';

            $fragment = new rex_fragment();
            $fragment->setVar('button_prefix', '<b>'.$currentRevision.'</b>', false);
            $fragment->setVar('items', $items, false);
            $fragment->setVar('toolbar', true);

            if (!$user->hasPerm('version[live_version]')) {
                $fragment->setVar('disabled', true);
            }

            $toolbar .= '<li class="dropdown">'.$fragment->parse('core/dropdowns/dropdown.php').'</li>';

            if (!$user->hasPerm('version[live_version]')) {
                if ($revision > 0) {
                    $toolbar .= '<li><a href="'.$context->getUrl(['rex_version_func' => 'copy_live_to_work']
                        ).'">'.rex_i18n::msg('version_copy_from_liveversion').'</a></li>';
                    $toolbar .= '<li><a href="'.rex_getUrl(
                            $articleId,
                            $clangId,
                            ['rex_version' => rex_article_revision::WORK]
                        ).'" rel="noopener noreferrer" target="_blank">'.rex_i18n::msg('version_preview').'</a></li>';
                }
            } else {
                if ($revision > 0) {
                    if (!$workingVersionEmpty) {
                        $toolbar .= '<li><a href="'.$context->getUrl(['rex_version_func' => 'clear_work']
                            ).'" data-confirm="'.rex_i18n::msg(
                                'version_confirm_clear_workingversion'
                            ).'">'.rex_i18n::msg('version_clear_workingversion').'</a></li>';
                        $toolbar .= '<li><a href="'.$context->getUrl(['rex_version_func' => 'copy_work_to_live']
                            ).'">'.rex_i18n::msg('version_working_to_live').'</a></li>';
                    }
                    $toolbar .= '<li><a href="'.rex_getUrl(
                            $articleId,
                            $clangId,
                            ['rex_version' => rex_article_revision::WORK]
                        ).'" rel="noopener noreferrer" target="_blank">'.rex_i18n::msg('version_preview').'</a></li>';
                } else {
                    $toolbar .= '<li><a href="'.$context->getUrl(['rex_version_func' => 'copy_live_to_work']
                        ).'" data-confirm="'.rex_i18n::msg(
                            'version_confirm_copy_live_to_workingversion'
                        ).'">'.rex_i18n::msg('version_copy_live_to_workingversion').'</a></li>';
                }
            }

            $inverse = rex_article_revision::WORK == $revision;
            $cssClass = rex_article_revision::WORK == $revision ? 'rex-state-inprogress' : 'rex-state-live';

            $return .= rex_view::toolbar('<ul class="nav navbar-nav">'.$toolbar.'</ul>', null, $cssClass, $inverse);

            return $return;
        });
    }
}

if (isset($REX['LOAD_PAGE']) && $REX['LOAD_PAGE']) {
    unset($REX);
    require rex_path::core(rex::isBackend() ? 'backend.php' : 'frontend.php');
}
