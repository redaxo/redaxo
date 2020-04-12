<?php

/**
 * @package redaxo5
 */

header('X-Robots-Tag: noindex, nofollow, noarchive');
header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: frame-ancestors 'self'");

// assets which are passed with a cachebuster will be cached very long,
// as we assume their url will change when the underlying content changes
if (rex_get('asset') && rex_get('buster')) {
    $assetFile = rex_get('asset');

    // relative to the assets-root
    if (0 === strpos($assetFile, '/assets/')) {
        $assetFile = '..'. $assetFile;
    }

    $fullPath = realpath($assetFile);
    $assetDir = rex_path::assets();

    if (0 !== strpos($fullPath, $assetDir)) {
        throw new Exception('Assets can only be streamed from within the assets folder. "'. $fullPath .'" is not within "'. $assetDir .'"');
    }

    $ext = rex_file::extension($assetFile);
    if ('js' === $ext) {
        $js = rex_file::get($assetFile);

        $js = preg_replace('@^//# sourceMappingURL=.*$@m', '', $js);

        rex_response::sendCacheControl('max-age=31536000, immutable');
        rex_response::sendContent($js, 'application/javascript');
    } elseif ('css' === $ext) {
        $styles = rex_file::get($assetFile);

        // If we are in a directory off the root, add a relative path here back to the root, like "../"
        // get the public path to this file, plus the baseurl
        $relativeroot = '';
        $pubroot = dirname($_SERVER['PHP_SELF']) . '/' . $relativeroot;

        $prefix = $pubroot . dirname($assetFile) . '/';
        $styles = preg_replace('/(url\(["\']?)([^\/"\'])([^\:\)]+["\']?\))/i', '$1' . $prefix .  '$2$3', $styles);

        rex_response::sendCacheControl('max-age=31536000, immutable');
        rex_response::sendContent($styles, 'text/css');
    } else {
        rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
        rex_response::sendContent('file not found');
    }
    exit();
}

// ----- verfuegbare seiten
$pages = [];
$page = '';

// ----------------- SETUP
if (rex::isSetup()) {
    // ----------------- SET SETUP LANG
    $requestLang = rex_request('lang', 'string');
    if (in_array($requestLang, rex_i18n::getLocales())) {
        rex::setProperty('lang', $requestLang);
    } else {
        rex::setProperty('lang', 'en_gb');
    }

    rex_i18n::setLocale(rex::getProperty('lang'));

    $pages['setup'] = rex_be_controller::getSetupPage();
    $page = 'setup';
    rex_be_controller::setCurrentPage('setup');
} else {
    // ----------------- CREATE LANG OBJ
    rex_i18n::setLocale(rex::getProperty('lang'));

    // ---- prepare login
    $login = new rex_backend_login();
    rex::setProperty('login', $login);

    $rex_user_login = rex_post('rex_user_login', 'string');
    $rex_user_psw = rex_post('rex_user_psw', 'string');
    $rex_user_stay_logged_in = rex_post('rex_user_stay_logged_in', 'boolean', false);

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

    $rex_user_loginmessage = '';

    if ($rex_user_login && !rex_csrf_token::factory('backend_login')->isValid()) {
        $loginCheck = rex_i18n::msg('csrf_token_invalid');
    } else {
        // the server side encryption of pw is only required
        // when not already encrypted by client using javascript
        $login->setLogin($rex_user_login, $rex_user_psw, rex_post('javascript', 'boolean'));
        $login->setStayLoggedIn($rex_user_stay_logged_in);
        $loginCheck = $login->checkLogin();
    }

    if (true !== $loginCheck) {
        if (rex_request::isXmlHttpRequest()) {
            rex_response::setStatus(rex_response::HTTP_UNAUTHORIZED);
        }

        // login failed
        $rex_user_loginmessage = $login->getMessage();

        // Fehlermeldung von der Datenbank
        if (is_string($loginCheck)) {
            $rex_user_loginmessage = $loginCheck;
        }

        $pages['login'] = rex_be_controller::getLoginPage();
        $page = 'login';
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
    }

    if ('' === $rex_user_loginmessage && rex_get('rex_logged_out', 'boolean')) {
        $rex_user_loginmessage = rex_i18n::msg('login_logged_out');
    }

    // Safe Mode
    if (null !== ($safeMode = rex_get('safemode', 'boolean', null))) {
        if ($safeMode) {
            rex_set_session('safemode', true);
        } else {
            rex_unset_session('safemode');
        }
    }
}

rex_be_controller::setPages($pages);

// ----- Prepare Core Pages
if (rex::getUser()) {
    rex_be_controller::appendLoggedInPages();
    rex_be_controller::setCurrentPage(trim(rex_request('page', 'string')));
}

rex_view::addJsFile(rex_url::coreAssets('jquery.min.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('jquery-ui.custom.min.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('jquery-pjax.min.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('standard.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('sha1.js'), [rex_view::JS_IMMUTABLE => true]);
rex_view::addJsFile(rex_url::coreAssets('clipboard-copy-element.js'), [rex_view::JS_IMMUTABLE => true]);

rex_view::setJsProperty('backend', true);
rex_view::setJsProperty('accesskeys', rex::getProperty('use_accesskeys'));
rex_view::setJsProperty('session_keep_alive', rex::getProperty('session_keep_alive', 0));

// ----- INCLUDE ADDONS
include_once rex_path::core('packages.php');

// ----- Prepare AddOn Pages
if (rex::getUser()) {
    rex_be_controller::appendPackagePages();
}

$pages = rex_extension::registerPoint(new rex_extension_point('PAGES_PREPARED', rex_be_controller::getPages()));
rex_be_controller::setPages($pages);

// Set Startpage
if ($user = rex::getUser()) {
    // --- page pruefen und benoetigte rechte checken
    rex_be_controller::checkPagePermissions($user);
}
$page = rex_be_controller::getCurrentPage();
rex_view::setJsProperty('page', $page);

// ----- EXTENSION POINT
// page variable validated
rex_extension::registerPoint(new rex_extension_point('PAGE_CHECKED', $page, ['pages' => $pages], true));

// trigger api functions
// If the backend session is timed out, rex_api_function would throw an exception
// so only trigger api functions if page != login
if ('login' != $page) {
    rex_api_function::handleCall();
}

// include the requested backend page
rex_be_controller::includeCurrentPage();

// ----- caching end für output filter
$CONTENT = ob_get_clean();

// ----- inhalt ausgeben
rex_response::sendPage($CONTENT);
