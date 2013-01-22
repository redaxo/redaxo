<?php

/**
 *
 * @package redaxo5
 */

// ----- pages, verfuegbare seiten
// array(name,addon=1,htmlheader=1);
$pages = array();
$page = '';

// ----------------- SETUP
if (rex::isSetup()) {
  // ----------------- SET SETUP LANG
  $requestLang = rex_request('lang', 'string');
  if (in_array($requestLang, rex_i18n::getLocales()))
    rex::setProperty('lang', $requestLang);

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

  if (rex_get('rex_logout', 'boolean'))
    $login->setLogout(true);

  // the server side encryption of pw is only required
  // when not already encrypted by client using javascript
  $login->setLogin($rex_user_login, $rex_user_psw, rex_post('javascript', 'boolean'));
  $login->setStayLoggedIn($rex_user_stay_logged_in);
  $loginCheck = $login->checkLogin();

  $rex_user_loginmessage = '';
  if ($loginCheck !== true) {
    rex_response::setStatus(rex_response::HTTP_UNAUTHORIZED);

    // login failed
    $rex_user_loginmessage = $login->message;

    // Fehlermeldung von der Datenbank
    if (is_string($loginCheck))
      $rex_user_loginmessage = $loginCheck;

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
  $pages = rex_be_controller::getLoggedInPages();
  rex_be_controller::setCurrentPage(trim(rex_request('page', 'string')));
}

rex_be_controller::setPages($pages);

// ----- INCLUDE ADDONS
include_once rex_path::core('packages.inc.php');

$pages = rex_be_controller::getPages();

// ----- Prepare AddOn Pages
if (rex::getUser()) {
  $pages = rex_be_controller::appendAddonPages($pages);
  rex_be_controller::setPages($pages);
}

$page = rex_be_controller::getCurrentPage();

// Set Startpage
if ($user = rex::getUser()) {
  // --- page pruefen und benoetigte rechte checken
  rex_be_controller::checkPage($user);
}

// ----- EXTENSION POINT
// page variable validated
rex_extension::registerPoint('PAGE_CHECKED', $page, array('pages' => $pages));

// trigger api functions
rex_api_function::handleCall();

// include the requested backend page
rex_be_controller::includeCurrentPage();

// ----- caching end für output filter
$CONTENT = ob_get_contents();
ob_end_clean();

// ----- inhalt ausgeben
rex_response::sendArticle($CONTENT);
