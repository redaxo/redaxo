<?php

header('X-Robots-Tag: noindex, nofollow, noarchive');
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: frame-ancestors 'self'");

// assets which are passed with a cachebuster will be cached very long,
// as we assume their url will change when the underlying content changes
if (rex_get('asset') && rex_get('buster')) {
    /** @psalm-taint-escape file */ // it is not escaped here, but it is validated below via the realpath
    $assetFile = rex_get('asset');

    // relative to the assets-root
    if (str_starts_with($assetFile, '/assets/')) {
        $assetFile = '..' . $assetFile;
    }

    $fullPath = realpath($assetFile);
    $assetDir = rex_path::assets();

    if (!str_starts_with($fullPath, $assetDir)) {
        throw new Exception('Assets can only be streamed from within the assets folder. "' . $fullPath . '" is not within "' . $assetDir . '"');
    }

    $ext = rex_file::extension($assetFile);
    if ('js' === $ext) {
        $js = rex_file::require($assetFile);

        $js = preg_replace('@^//# sourceMappingURL=.*$@m', '', $js);

        rex_response::sendCacheControl('max-age=31536000, immutable');
        rex_response::sendContent($js, 'application/javascript');
    } elseif ('css' === $ext) {
        $styles = rex_file::require($assetFile);

        // If we are in a directory off the root, add a relative path here back to the root, like "../"
        // get the public path to this file, plus the baseurl
        $relativeroot = '';
        $pubroot = dirname($_SERVER['PHP_SELF']) . '/' . $relativeroot;

        $prefix = $pubroot . dirname($assetFile) . '/';
        $styles = preg_replace('/(url\(["\']?)([^\/"\'])([^\:\)]+["\']?\))/i', '$1' . $prefix . '$2$3', $styles);

        rex_response::sendCacheControl('max-age=31536000, immutable');
        rex_response::sendContent($styles, 'text/css');
    } else {
        rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
        rex_response::sendContent('file not found');
    }
    exit;
}

// ----- verfuegbare seiten
$pages = [];

// ----------------- SETUP
if (rex::isSetup()) {
    // ----------------- SET SETUP LANG
    $requestLang = rex_request('lang', 'string', rex::getProperty('lang'));
    if (in_array($requestLang, rex_i18n::getLocales())) {
        rex::setProperty('lang', $requestLang);
    } else {
        rex::setProperty('lang', 'en_gb');
    }

    rex_i18n::setLocale(rex::getProperty('lang'));

    $pages['setup'] = rex_be_controller::getSetupPage();
    rex_be_controller::setCurrentPage('setup');
} else {
    // ----------------- CREATE LANG OBJ
    rex_i18n::setLocale(rex::getProperty('lang'));

    // ---- prepare login
    $login = new rex_backend_login();
    rex::setProperty('login', $login);

    $passkey = rex_post('rex_user_passkey', 'string', null);
    $rexUserLogin = rex_post('rex_user_login', 'string');
    $rexUserPsw = rex_post('rex_user_psw', 'string');
    $rexUserStayLoggedIn = rex_post('rex_user_stay_logged_in', 'boolean', false);

    if (rex_get('rex_logout', 'boolean') && rex_csrf_token::factory('backend_logout')->isValid()) {
        $login->setLogout(true);
        $login->checkLogin();
        rex_csrf_token::removeAll();

        $userAgent = rex_server('HTTP_USER_AGENT');
        $advertisedChrome = preg_match('/(Chrome|CriOS)\//i', $userAgent);
        $nonChrome = preg_match('/(Aviator|ChromePlus|coc_|Dragon|Edge|Flock|Iron|Kinza|Maxthon|MxNitro|Nichrome|OPR|Perk|Rockmelt|Seznam|Sleipnir|Spark|UBrowser|Vivaldi|WebExplorer|YaBrowser)/i', $userAgent);
        if ($advertisedChrome && !$nonChrome) {
            // Browser is likely Google Chrome which currently seems to be super slow when clearing 'cache' from site data
            // https://bugs.chromium.org/p/chromium/issues/detail?id=762417
            rex_response::setHeader('Clear-Site-Data', '"storage", "executionContexts"');
        } else {
            rex_response::setHeader('Clear-Site-Data', '"cache", "storage", "executionContexts"');
        }

        // Currently browsers like Safari do not support the header Clear-Site-Data.
        // we dont kill/regenerate the session so e.g. the frontend will not get logged out
        rex_request::clearSession();

        // is necessary for login after logout
        // and without the redirect, the csrf token would be invalid
        rex_response::sendRedirect(rex_url::backendController(['rex_logged_out' => 1]));
    }

    $rexUserLoginmessage = '';

    if (($rexUserLogin || $passkey) && !rex_csrf_token::factory('backend_login')->isValid()) {
        $loginCheck = rex_i18n::msg('csrf_token_invalid');
    } else {
        // the server side encryption of pw is only required
        // when not already encrypted by client using javascript
        $login->setLogin($rexUserLogin, $rexUserPsw, rex_post('javascript', 'boolean'));
        $login->setPasskey('' === $passkey ? null : $passkey);
        $login->setStayLoggedIn($rexUserStayLoggedIn);
        $loginCheck = $login->checkLogin();
    }

    if (true !== $loginCheck) {
        if (rex_request::isXmlHttpRequest()) {
            rex_response::setStatus(rex_response::HTTP_UNAUTHORIZED);
        }

        // login failed
        $rexUserLoginmessage = $login->getMessage();

        // Fehlermeldung von der Datenbank
        if (is_string($loginCheck)) {
            $rexUserLoginmessage = $loginCheck;
        }

        $pages['login'] = rex_be_controller::getLoginPage();
        rex_be_controller::setCurrentPage('login');

        if ('login' !== rex_request('page', 'string', 'login')) {
            // clear in-browser data of a previous session with the same browser for security reasons.
            // a possible attacker should not be able to access cached data of a previous valid session on the same computer.
            // clearing "executionContext" or "cookies" would result in a endless loop.
            rex_response::setHeader('Clear-Site-Data', '"cache", "storage"');

            // Currently browsers like Safari do not support the header Clear-Site-Data.
            // we dont kill/regenerate the session so e.g. the frontend will not get logged out
            rex_request::clearSession();
        }
    } else {
        // Userspezifische Sprache einstellen
        $user = $login->getUser();
        $lang = $user->getLanguage();
        if ($lang && 'default' != $lang && $lang != rex::getProperty('lang')) {
            rex_i18n::setLocale($lang);
        }

        rex::setProperty('user', $user);

        // Safe Mode
        if (!rex::isLiveMode() && $user->isAdmin() && null !== ($safeMode = rex_get('safemode', 'boolean', null))) {
            if ($safeMode) {
                rex_set_session('safemode', true);
            } else {
                rex_unset_session('safemode');
                if (rex::getProperty('safe_mode')) {
                    $configFile = rex_path::coreData('config.yml');
                    $config = array_merge(
                        rex_file::getConfig(rex_path::core('default.config.yml')),
                        rex_file::getConfig($configFile),
                    );
                    $config['safe_mode'] = false;
                    rex_file::putConfig($configFile, $config);
                }
            }
        }
    }

    if ('' === $rexUserLoginmessage && rex_get('rex_logged_out', 'boolean')) {
        $rexUserLoginmessage = rex_i18n::msg('login_logged_out');
    }
}

rex_be_controller::setPages($pages);

// ----- Prepare Core Pages
if (rex::getUser()) {
    rex_be_controller::setCurrentPage(trim(rex_request('page', 'string')));
    rex_be_controller::appendLoggedInPages();

    if ('profile' !== rex_be_controller::getCurrentPage() && rex::getProperty('login')->requiresPasswordChange()) {
        rex_response::sendRedirect(rex_url::backendPage('profile'));
    }
}

rex_view::addJsFile(rex_url::coreAssets('jquery.min.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('jquery-ui.custom.min.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('jquery-pjax.min.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('standard.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('sha1.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('clipboard-copy-element.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('js/mediapool.js'), [rex_view::JS_IMMUTABLE]);

rex_view::setJsProperty('backend', true);
rex_view::setJsProperty('accesskeys', rex::getProperty('use_accesskeys'));
rex_view::setJsProperty('session_keep_alive', rex::getProperty('session_keep_alive', 0));
rex_view::setJsProperty('cookie_params', rex_login::getCookieParams());
rex_view::setJsProperty('imageExtensions', rex::getProperty('image_extensions'));

rex_view::addCssFile(rex_url::coreAssets('css/styles.css'));
rex_view::addCssFile(rex_url::coreAssets('css/bootstrap-select.min.css'));
rex_view::addJsFile(rex_url::coreAssets('js/bootstrap.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('js/bootstrap-select.min.js'), [rex_view::JS_IMMUTABLE => true]);
$bootstrapSelectLang = [
    'de_de' => 'de_DE',
    'en_gb' => 'en_US',
    'es_es' => 'de_DE',
    'it_it' => 'it_IT',
    'nl_nl' => 'nl_NL',
    'pt_br' => 'pt_BR',
    'sv_se' => 'sv_SE',
][rex_i18n::getLocale()] ?? 'en_US';
rex_view::addJsFile(rex_url::coreAssets('js/bootstrap-select-defaults-' . $bootstrapSelectLang . '.min.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('js/main.js'), [rex_view::JS_IMMUTABLE => true]);

rex_view::addCssFile(rex_url::coreAssets('css/redaxo.css'));
rex_view::addJsFile(rex_url::coreAssets('js/redaxo.js'), [rex_view::JS_IMMUTABLE => true]);

if (rex::getUser()) {
    /* Customizer Ergänzungen */
    rex_view::addCssFile(rex_url::coreAssets('css/customizer.css'));
    rex_view::addJsFile(rex_url::coreAssets('js/customizer.js'), [rex_view::JS_IMMUTABLE => true]);

    if ('' != rex::getConfig('be_style_labelcolor')) {
        rex_view::setJsProperty('customizer_labelcolor', rex::getConfig('be_style_labelcolor'));
    }
    if (rex::getConfig('be_style_showlink')) {
        rex_view::setJsProperty(
            'customizer_showlink',
            '<h1 class="be-style-customizer-title"><a href="' . rex_url::frontend() . '" target="_blank" rel="noreferrer noopener"><span class="be-style-customizer-title-name">' . rex_escape(rex::getServerName()) . '</span><i class="rex-icon rex-icon-external-link"></i></a></h1>',
        );
    }

    rex_view::addJsFile(rex_url::coreAssets('js/linkmap.js'), [rex_view::JS_IMMUTABLE => true]);

    if ('content' == rex_be_controller::getCurrentPagePart(1)) {
        rex_view::addJsFile(rex_url::coreAssets('js/content.js'), [rex_view::JS_IMMUTABLE => true]);
    }
}

if (rex::getConfig('article_history', false) && rex::getUser()?->hasPerm('history[article_rollback]')) {
    rex_extension::register(
        ['ART_SLICES_COPY', 'SLICE_ADD', 'SLICE_UPDATE', 'SLICE_MOVE', 'SLICE_DELETE'],
        static function (rex_extension_point $ep) {
            $type = match ($ep->getName()) {
                'ART_SLICES_COPY' => 'slices_copy',
                'SLICE_MOVE' => 'slice_' . $ep->getParam('direction'),
                default => strtolower($ep->getName()),
            };

            $articleId = $ep->getParam('article_id');
            $clangId = $ep->getParam('clang_id');
            $sliceRevision = $ep->getParam('slice_revision');

            if (0 == $sliceRevision) {
                rex_article_slice_history::makeSnapshot($articleId, $clangId, $type);
            }
        },
    );

    rex_view::addCssFile(rex_url::coreAssets('noUiSlider/nouislider.css'));
    rex_view::addJsFile(rex_url::coreAssets('noUiSlider/nouislider.js'), [rex_view::JS_IMMUTABLE => true]);
    rex_view::addCssFile(rex_url::coreAssets('css/history.css'));
    rex_view::addJsFile(rex_url::coreAssets('js/history.js'), [rex_view::JS_IMMUTABLE => true]);

    switch (rex_request('rex_history_function', 'string')) {
        case 'snap':
            $articleId = rex_request('history_article_id', 'int');
            $clangId = rex_request('history_clang_id', 'int');
            $historyDate = rex_request('history_date', 'string');
            rex_article_slice_history::restoreSnapshot($historyDate, $articleId, $clangId);

            // no break
        case 'layer':
            $articleId = rex_request('history_article_id', 'int');
            $clangId = rex_request('history_clang_id', 'int');
            $versions = rex_article_slice_history::getSnapshots($articleId, $clangId);

            $select1 = [];
            $select1[] = '<option value="0" selected="selected" data-revision="0">' . rex_i18n::msg('structure_history_current_version') . '</option>';
            if (true === rex::getConfig('article_work_version', false)) {
                $select1[] = '<option value="1" data-revision="1">' . rex_i18n::msg('version_workingversion') . '</option>';
            }

            $select2 = [];
            $select2[] = '<option value="" selected="selected">' . rex_i18n::msg('structure_history_current_version') . '</option>';
            foreach ($versions as $version) {
                $historyInfo = $version['history_date'];
                if ('' != $version['history_user']) {
                    $historyInfo = $version['history_date'] . ' [' . $version['history_user'] . ']';
                }
                $select2[] = '<option value="' . strtotime($version['history_date']) . '" data-history-date="' . rex_escape($version['history_date']) . '">' . rex_escape($historyInfo) . '</option>';
            }

            $content1select = '<select id="content-history-select-date-1" class="content-history-select" data-iframe="content-history-iframe-1" style="">' . implode('', $select1) . '</select>';
            $content1iframe = '<iframe id="content-history-iframe-1" class="history-iframe"></iframe>';
            $content2select = '<select id="content-history-select-date-2" class="content-history-select" data-iframe="content-history-iframe-2">' . implode('', $select2) . '</select>';
            $content2iframe = '<iframe id="content-history-iframe-2" class="history-iframe"></iframe>';

            // fragment holen und ausgeben
            $fragment = new rex_fragment();
            $fragment->setVar('title', rex_i18n::msg('structure_history_overview_versions'));
            $fragment->setVar('content1select', $content1select, false);
            $fragment->setVar('content1iframe', $content1iframe, false);
            $fragment->setVar('content2select', $content2select, false);
            $fragment->setVar('content2iframe', $content2iframe, false);

            echo $fragment->parse('core/structure/history/layer.php');
            exit;
    }

    rex_extension::register('STRUCTURE_CONTENT_HEADER', static function (rex_extension_point $ep) {
        if ('content/edit' == $ep->getParam('page')) {
            $articleLink = rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId());
            if (str_starts_with($articleLink, 'http')) {
                $user = rex::requireUser();
                $userLogin = $user->getLogin();
                $historyValidTime = new DateTime();
                $historyValidTime = $historyValidTime->modify('+10 Minutes')->format('YmdHis'); // 10 minutes valid key
                $userHistorySession = rex_history_login::createSessionKey($userLogin, $user->getValue('session_id'), $historyValidTime);
                $articleLink = rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId(), [
                    rex_history_login::class => $userLogin,
                    'rex_history_session' => $userHistorySession,
                    'rex_history_validtime' => $historyValidTime,
                ]);
            }

            echo '<script nonce="' . rex_response::getNonce() . '">
                    var history_article_id = ' . rex_article::getCurrentId() . ';
                    var history_clang_id = ' . rex_clang::getCurrentId() . ';
                    var history_ctype_id = ' . rex_request('ctype', 'int', 0) . ';
                    var history_article_link = "' . $articleLink . '";
                </script>';
        }
    });
}

if (rex::getConfig('article_work_version', false)) {
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
            'select * from ' . rex::getTablePrefix(
            ) . 'article_slice where article_id=? and clang_id=? and revision=1 LIMIT 1',
            [$articleId, $clangId],
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
                    if (true === rex::getConfig('article_history', false)) {
                        rex_article_slice_history::makeSnapshot($articleId, $clangId, 'work_to_live');
                    }

                    rex_article_revision::copyContent(
                        $articleId,
                        $clangId,
                        rex_article_revision::WORK,
                        rex_article_revision::LIVE,
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
        $fragment->setVar('button_prefix', '<b>' . $currentRevision . '</b>', false);
        $fragment->setVar('items', $items, false);
        $fragment->setVar('toolbar', true);

        if (!$user->hasPerm('version[live_version]')) {
            $fragment->setVar('disabled', true);
        }

        $toolbar .= '<li class="dropdown">' . $fragment->parse('core/dropdowns/dropdown.php') . '</li>';

        if (!$user->hasPerm('version[live_version]')) {
            if ($revision > 0) {
                $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'copy_live_to_work']) . '">' . rex_i18n::msg('version_copy_from_liveversion') . '</a></li>';
                $toolbar .= '<li><a href="' . rex_getUrl($articleId, $clangId, ['rex_version' => rex_article_revision::WORK]) . '" rel="noopener noreferrer" target="_blank">' . rex_i18n::msg('version_preview') . '</a></li>';
            }
        } else {
            if ($revision > 0) {
                if (!$workingVersionEmpty) {
                    $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'clear_work']) . '" data-confirm="' . rex_i18n::msg('version_confirm_clear_workingversion') . '">' . rex_i18n::msg('version_clear_workingversion') . '</a></li>';
                    $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'copy_work_to_live']) . '">' . rex_i18n::msg('version_working_to_live') . '</a></li>';
                }
                $toolbar .= '<li><a href="' . rex_getUrl($articleId, $clangId, ['rex_version' => rex_article_revision::WORK]) . '" rel="noopener noreferrer" target="_blank">' . rex_i18n::msg('version_preview') . '</a></li>';
            } else {
                $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'copy_live_to_work']) . '" data-confirm="' . rex_i18n::msg('version_confirm_copy_live_to_workingversion') . '">' . rex_i18n::msg('version_copy_live_to_workingversion') . '</a></li>';
            }
        }

        $inverse = rex_article_revision::WORK == $revision;
        $cssClass = rex_article_revision::WORK == $revision ? 'rex-state-inprogress' : 'rex-state-live';

        $return .= rex_view::toolbar('<ul class="nav navbar-nav">' . $toolbar . '</ul>', null, $cssClass, $inverse);

        return $return;
    });
}

// add theme-information to js-variable rex as rex.theme
// (1) System-Settings (2) no systemforced mode: user-mode (3) fallback: "auto"
$user = rex::getUser();
$theme = (string) rex::getProperty('theme');
if ('' === $theme && $user) {
    $theme = (string) $user->getValue('theme');
}
rex_view::setJsProperty('theme', $theme ?: 'auto');

if ('system' == rex_be_controller::getCurrentPagePart(1)) {
    rex_system_setting::register(new rex_system_setting_article_id('start_article_id'));
    rex_system_setting::register(new rex_system_setting_article_id('notfound_article_id'));
    rex_system_setting::register(new rex_system_setting_default_template_id());
    rex_system_setting::register(new rex_system_setting_structure_package_status('article_history'));
    rex_system_setting::register(new rex_system_setting_structure_package_status('article_work_version'));
    rex_system_setting::register(new rex_system_setting_phpmailer_errormail());
}
if ('content' == rex_be_controller::getCurrentPagePart(1)) {
    rex_view::addCssFile(rex_url::coreAssets('css/metainfo.css'));
    rex_view::addJsFile(rex_url::coreAssets('js/metainfo.js'));
}

rex_perm::register('users[]');

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
rex_perm::register('moveSlice[]', null, rex_perm::OPTIONS);
rex_perm::register('publishSlice[]', null, rex_perm::OPTIONS);

if (rex::getConfig('article_history', false)) {
    rex_perm::register('history[article_rollback]', null, rex_perm::OPTIONS);
}
if (rex::getConfig('article_work_version', false)) {
    rex_perm::register('version[live_version]', null, rex_perm::OPTIONS);
}

// Metainfo
rex::setProperty('metainfo_prefixes', ['art_', 'cat_', 'med_', 'clang_']);
rex::setProperty('metainfo_metaTables', [
    'art_' => rex::getTablePrefix() . 'article',
    'cat_' => rex::getTablePrefix() . 'article',
    'med_' => rex::getTablePrefix() . 'media',
    'clang_' => rex::getTablePrefix() . 'clang',
]);

require_once __DIR__ . '/functions/function_metainfo.php';

rex_extension::register('PAGE_CHECKED', 'rex_metainfo_extensions_handler');
rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', function ($ep) {
    $subject = $ep->getSubject();
    $metaSidebar = include rex_path::core('pages/metainfo.content.php');
    return $metaSidebar . $subject;
});

// ----- INCLUDE ADDONS
include_once rex_path::core('packages.php');

if (rex::getUser() && rex::getConfig('be_style_compile')) {
    rex_be_style::compile();
}

// ----- Prepare AddOn Pages
if (rex::getUser()) {
    rex_be_controller::appendPackagePages();
}

$pages = rex_extension::registerPoint(new rex_extension_point('PAGES_PREPARED', rex_be_controller::getPages()));
rex_be_controller::setPages($pages);

// Set Startpage
if ($user = rex::getUser()) {
    if (rex::getProperty('login')->requiresPasswordChange()) {
        // profile is available for everyone, no additional checks required
        rex_be_controller::setCurrentPage('profile');
    } elseif (!rex_be_controller::getCurrentPage()) {
        // trigger api functions before page permission check/redirection, if page param is not set.
        // the api function is responsible for checking permissions.
        rex_api_function::handleCall();
    }

    // --- page pruefen und benoetigte rechte checken
    rex_be_controller::checkPagePermissions($user);
}
$page = rex_be_controller::getCurrentPage();
rex_view::setJsProperty('page', $page);

if ('content' == rex_be_controller::getCurrentPagePart(1)) {
    rex_be_controller::getPageObject('structure')->setIsActive(true);
}

// ----- EXTENSION POINT
// page variable validated
rex_extension::registerPoint(new rex_extension_point('PAGE_CHECKED', $page, ['pages' => $pages], true));

if (in_array($page, ['profile', 'login'], true)) {
    rex_view::addJsFile(rex_url::coreAssets('webauthn.js'), [rex_view::JS_IMMUTABLE => true]);
}

if ($page) {
    // trigger api functions after PAGE_CHECKED, if page param is set
    // the api function is responsible for checking permissions.
    rex_api_function::handleCall();
}

// include the requested backend page
rex_be_controller::includeCurrentPage();

// ----- caching end für output filter
$CONTENT = ob_get_clean();

// ----- inhalt ausgeben
rex_response::sendPage($CONTENT);
