<?php

/**
 * @package redaxo5
 */

header('X-Robots-Tag: noindex, nofollow, noarchive');
header('X-Frame-Options: SAMEORIGIN');

// ----- pages, verfuegbare seiten
// array(name,addon=1,htmlheader=1);
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

    if (rex_get('rex_logout', 'boolean')) {
        $login->setLogout(true);
    }

    // the server side encryption of pw is only required
    // when not already encrypted by client using javascript
    $login->setLogin($rex_user_login, $rex_user_psw, rex_post('javascript', 'boolean'));
    $login->setStayLoggedIn($rex_user_stay_logged_in);
    $loginCheck = $login->checkLogin();

    $rex_user_loginmessage = '';
    if ($loginCheck !== true) {
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
    } else {
        // Userspezifische Sprache einstellen
        $user = $login->getUser();
        $lang = $user->getLanguage();
        if ($lang && $lang != 'default' && $lang != rex::getProperty('lang')) {
            rex_i18n::setLocale($lang);
        }

        rex::setProperty('user', $user);
    }

    // Safe Mode
    if (($safeMode = rex_get('safemode', 'boolean', null)) !== null) {
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

rex_view::addJsFile(rex_url::coreAssets('jquery.min.js'));
rex_view::addJsFile(rex_url::coreAssets('jquery-ui.custom.min.js'));
rex_view::addJsFile(rex_url::coreAssets('jquery-pjax.min.js'));
rex_view::addJsFile(rex_url::coreAssets('standard.js'));
rex_view::addJsFile(rex_url::coreAssets('sha1.js'));

rex_view::setJsProperty('backend', true);
rex_view::setJsProperty('accesskeys', rex::getProperty('use_accesskeys'));

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
if ($page != 'login') {
    rex_api_function::handleCall();
}

// include the requested backend page
rex_be_controller::includeCurrentPage();

// ----- caching end für output filter
$CONTENT = ob_get_clean();

// ----- inhalt ausgeben
rex_response::sendPage($CONTENT);
