<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

// ----- caching start für output filter
ob_start();
ob_implicit_flush(0);


require_once rex_path::core('master.inc.php');

// ----- addon/normal page path
$REX['PAGEPATH'] = '';

// ----- pages, verfuegbare seiten
// array(name,addon=1,htmlheader=1);
$REX['PAGES'] = array();
$page = '';

// ----------------- SETUP
if (rex_core::isSetup())
{
  // ----------------- SET SETUP LANG
  $requestLang = rex_request('lang', 'string');
  if(in_array($requestLang, rex_i18n::getLocales()))
    rex_core::setProperty('lang', $requestLang);

  rex_i18n::setLocale($REX['LANG']);

  $REX['PAGES']['setup'] = rex_be_navigation::getSetupPage();
  rex_core::setProperty('page', "setup");

}else
{
  // ----------------- CREATE LANG OBJ
  rex_i18n::setLocale(rex_core::getProperty('lang'));

  // ---- prepare login
  $login = new rex_backend_login();
  rex_core::setProperty('login', $login);

  $rex_user_login = rex_post('rex_user_login', 'string');
  $rex_user_psw = rex_post('rex_user_psw', 'string');

  // the service side encryption of pw is only required
  // when not already encrypted by client using javascript
  if (rex_core::getProperty('pswfunc') != '' && rex_post('javascript') == '0')
    rex_core::getProperty('login')->setPasswordFunction(rex_core::getProperty('pswfunc'));

  if (rex_get('rex_logout', 'boolean'))
    $login->setLogout(true);

  $login->setLogin($rex_user_login, $rex_user_psw);
  $loginCheck = $login->checkLogin();

  $rex_user_loginmessage = "";
  if ($loginCheck !== true)
  {
    // login failed
    $rex_user_loginmessage = $login->message;

    // Fehlermeldung von der Datenbank
    if(is_string($loginCheck))
      $rex_user_loginmessage = $loginCheck;

    $REX['PAGES']['login'] = rex_be_navigation::getLoginPage();
    $page = 'login';
    rex_core::setProperty('page', 'login');
  }
  else
  {
    // Userspezifische Sprache einstellen
    $lang = $login->getLanguage();
    if($lang != 'default' && $lang != rex_core::getProperty('lang'))
    {
      rex_i18n::setLocale($lang);
    }

    rex_core::setProperty('user', $login->USER);
  }
}

// ----- Prepare Core Pages
if(rex_core::getUser())
{
  $REX['PAGES'] = rex_be_navigation::getLoggedInPages();
}

// ----- INCLUDE ADDONS
include_once rex_path::core('packages.inc.php');

// ----- Prepare AddOn Pages
if(rex_core::getUser())
{
  foreach(rex_addon::getAvailableAddons() as $addonName => $addon)
  {
    $page  = $addon->getProperty('page', null);
    $title = $addon->getProperty('name', '');
    $href  = $addon->getProperty('link',  'index.php?page='. $addonName);
    $perm  = $addon->getProperty('perm', '');

    // prepare addons root-page
    $addonPage = null;
    if ($page != null && $page instanceof rex_be_page_container && $page->getPage()->checkPermission(rex_core::getUser()))
    {
        $addonPage = $page;
    }
    else if($perm == '' || rex_core::getUser()->hasPerm($perm) || rex_core::getUser()->isAdmin())
    {
      if ($title != '')
      {
        $addonPage = new rex_be_page($title, array('page' => $addonName));
        $addonPage->setHref($href);

        // wegen REX Version = 4.2 - alter Stil "SUBPAGES"
        // TODO im compat addon erledigen
        if(isset($REX['ADDON'][$addonName]['SUBPAGES']))
        {
          $addon->setProperty('pages', $REX['ADDON'][$addonName]['SUBPAGES']);
        }
        // *** ENDE wegen <=4.2
      }
    }

    if($addonPage)
    {
      // adds be_page's
      foreach($addon->getProperty('pages', array()) as $s)
      {
        if (is_array($s))
        {
          if (!isset($s[2]) || rex_core::getUser()->hasPerm($s[2]) || rex_core::getUser()->isAdmin())
          {
            $subPage = new rex_be_page($s[1], array('page' => $addonName, 'subpage' => $s[0]));
            $subPage->setHref('index.php?page='.$addonName.'&subpage='.$s[0]);
            $addonPage->addSubPage($subPage);
          }
        } else if (rex_be_page_main::isValid($s))
        {
          $p = $s->getPage();
          $REX['PAGES'][$addonName.'_'.$p->getTitle()] = $s;
        } else if (rex_be_page::isValid($s) && $addonPage)
        {
          $addonPage->addSubPage($s);
        }
      }
    }

    // handle plugins
    foreach($addon->getAvailablePlugins() as $pluginName => $plugin)
    {
      $page  = $plugin->getProperty('page', null);

      $title = $plugin->getProperty('name', '');
      $href  = $plugin->getProperty('link',  'index.php?page='. $addonName . '&subpage='. $pluginName);
      $perm  = $plugin->getProperty('perm', '');

      // prepare plugins root-page
      $pluginPage = null;
      if ($page != null && $page instanceof rex_be_page_container && $page->getPage()->checkPermission(rex_core::getUser()))
      {
          $pluginPage = $page;
      }
      else if ($perm == '' || rex_core::getUser()->hasPerm($perm) || rex_core::getUser()->isAdmin())
      {
        if($title != '')
        {
          $pluginPage = new rex_be_page($title, array('page' => $addonName, 'subpage' => $pluginName));
          $pluginPage->setHref($href);
        }
      }

      // add plugin-be_page's to addon
      foreach($plugin->getProperty('pages', array()) as $s)
      {
        if(is_array($s) && $addonPage)
        {
          if (!isset($s[2]) || rex_core::getUser()->hasPerm($s[2]) || rex_core::getUser()->isAdmin())
          {
            $subPage = new rex_be_page($s[1], array('page' => $addonName, 'subpage' => $s[0]));
            $subPage->setHref('index.php?page='.$addonName.'&subpage='.$s[0]);
            $addonPage->addSubPage($subPage);
          }
        }
        else if(rex_be_page_main::isValid($s))
        {
          $p = $s->getPage();
          $REX['PAGES'][$addonName.'_'.$pluginName.'_'.$p->getTitle()] = $s;
        }
        else if(rex_be_page::isValid($s) && $addonPage)
        {
          $addonPage->addSubPage($s);
        }
      }

      if($pluginPage)
      {
        if(rex_be_page_main::isValid($pluginPage))
        {
          if(!$pluginPage->getPage()->hasPath())
          {
            $pagePath = rex_path::plugin($addonName, $pluginName, 'pages/index.inc.php');
            $pluginPage->getPage()->setPath($pagePath);
          }
          $REX['PAGES'][$pluginName] = $pluginPage;
        }
        else
        {
          // "navigation" adds attributes to the plugin-root page
          $navProperties = $plugin->getProperty('navigation', array());
          // if there are some navigation attributes set, create a main page and apply attributes to it
          if(count($navProperties) > 0)
          {
            $mainPluginPage = new rex_be_page_main($addonName, $pluginPage);
            foreach($navProperties as $key => $value)
            {
              $mainPluginPage->_set($key, $value);
            }
            $REX['PAGES'][$addonName.'_'.$pluginName] = $mainPluginPage;
          }
          // if no navigation attributes can be found, we add the pluginPage as subPage of the addon
          else if($addonPage)
          {
            $addonPage->addSubPage($pluginPage);
          }
        }
      }
    }

    if(rex_be_page_main::isValid($addonPage))
    {
      // addonPage was defined as a main-page itself, so we only need to add it to REX
      $REX['PAGES'][$addonName] = $addonPage;
    }
    else
    {
      // wrap the be_page into a main_page
      $mainAddonPage = null;
      if ($addonPage)
      {
        $mainAddonPage = new rex_be_page_main('addons', $addonPage);

        // "navigation" adds attributes to the addon-root page
        foreach($addon->getProperty('navigation', array()) as $key => $value)
        {
          $mainAddonPage->_set($key, $value);
        }
        $REX['PAGES'][$addonName] = $mainAddonPage;
      }
    }
  }
}

// Set Startpage
if($user = rex_core::getUser())
{
  $user->pages = $REX['PAGES'];

  // --- page herausfinden
  $page = trim(rex_request('page', 'string'));

  // --- page pruefen und benoetigte rechte checken
  if(!isset($REX['PAGES'][$page]) ||
    (($p=$REX['PAGES'][$page]->getPage()) && !$p->checkPermission($user)))
  {
    // --- neue page bestimmen und diese in neuem request dann verarbeiten
    $page = rex_core::getProperty('login')->getStartpage();
    if(!isset($REX['PAGES'][$page]) ||
      (($p=$REX['PAGES'][$page]->getPage()) && !$p->checkPermission($user)))
    {
      $page = rex_core::getProperty('start_page');
      if(!isset($REX['PAGES'][$page]) ||
        (($p=$REX['PAGES'][$page]->getPage()) && !$p->checkPermission($user)))
      {
        // --- fallback auf "profile"; diese page hat jeder user
        $page = 'profile';
      }
    }

    header('Location: index.php?page='. $page);
    exit();
  }
}

rex_core::setProperty('page', $page);
$pageObj = $REX['PAGES'][$page]->getPage();
$REX['PAGE_NO_NAVI'] = !$pageObj->hasNavigation();


// ----- EXTENSION POINT
// page variable validated
rex_extension::registerPoint( 'PAGE_CHECKED', $page, array('pages' => $REX['PAGES']));

// trigger api functions
rex_api_function::handleCall();

if($pageObj->hasLayout())
{
  require rex_path::core('layout/top.php');
}

$path = '';
$pageObj = $REX['PAGES'][$page]->getPage();
if($pageObj->hasPath())
{
  // If page has a new/overwritten path
  $path = $pageObj->getPath();
  if(preg_match('@'. preg_quote(rex_path::version('addons/'), '@') .'([^/\\\]+)(?:[/\\\]plugins[/\\\]([^/\\\]+))?@', $path, $matches))
  {
    $package = rex_addon::get($matches[1]);
    if(isset($matches[2]))
    {
      $package = $package->getPlugin($matches[2]);
    }
    rex_packageManager::includeFile($package, str_replace($package->getBasePath(), '', $path));
  }
  else
  {
    require $path;
  }
}
else if($pageObj->isCorePage())
{
  // Core Page
  require rex_path::core('pages/'. $page .'.inc.php');
}
else
{
  // Addon Page
  rex_addonManager::includeFile(rex_addon::get($page), 'pages/index.inc.php');
}

if($pageObj->hasLayout())
{
  require rex_path::core('layout/bottom.php');
}

// ----- caching end für output filter
$CONTENT = ob_get_contents();
ob_end_clean();

// ----- inhalt ausgeben
rex_response::sendArticle(null, $CONTENT, 'backend', TRUE);