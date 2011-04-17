<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

// ----- caching start für output filter
ob_start();
ob_implicit_flush(0);

// ----------------- MAGIC QUOTES CHECK
// require_once rex_path::core('/functions/function_rex_mquotes.inc.php');


require_once rex_path::src('config/master.inc.php');

// ----- addon/normal page path
$REX['PAGEPATH'] = '';

// ----- pages, verfuegbare seiten
// array(name,addon=1,htmlheader=1);
$REX['PAGES'] = array();
$REX['PAGE'] = '';

// ----------------- SETUP
$REX['USER'] = NULL;
$REX['LOGIN'] = NULL;

if ($REX['SETUP'])
{
  // ----------------- SET SETUP LANG
  $requestLang = rex_request('lang', 'string');
  if(in_array($requestLang, rex_i18n::getLocales()))
    $REX['LANG'] = $requestLang;

  rex_i18n::setLocale($REX['LANG']);

  $REX['PAGES']['setup'] = rex_be_navigation::getSetupPage();
  $REX['PAGE'] = "setup";

}else
{
  // ----------------- CREATE LANG OBJ
  rex_i18n::setLocale($REX['LANG']);

  // ---- prepare login
  $REX['LOGIN'] = new rex_backend_login();
  $rex_user_login = rex_post('rex_user_login', 'string');
  $rex_user_psw = rex_post('rex_user_psw', 'string');

  // the service side encryption of pw is only required
  // when not already encrypted by client using javascript
  if ($REX['PSWFUNC'] != '' && rex_post('javascript') == '0')
    $REX['LOGIN']->setPasswordFunction($REX['PSWFUNC']);

  if (rex_get('rex_logout', 'boolean'))
    $REX['LOGIN']->setLogout(true);

  $REX['LOGIN']->setLogin($rex_user_login, $rex_user_psw);
  $loginCheck = $REX['LOGIN']->checkLogin();

  $rex_user_loginmessage = "";
  if ($loginCheck !== true)
  {
    // login failed
    $rex_user_loginmessage = $REX['LOGIN']->message;

    // Fehlermeldung von der Datenbank
    if(is_string($loginCheck))
      $rex_user_loginmessage = $loginCheck;

    $REX['PAGES']['login'] = rex_be_navigation::getLoginPage();
    $REX['PAGE'] = 'login';

    $REX['USER'] = null;
    $REX['LOGIN'] = null;
  }
  else
  {
    // Userspezifische Sprache einstellen
    $lang = $REX['LOGIN']->getLanguage();
    if($lang != 'default' && $lang != $REX['LANG'])
    {
      rex_i18n::setLocale($lang);
    }

    $REX['USER'] = $REX['LOGIN']->USER;
  }
}

// ----- Prepare Core Pages
if($REX['USER'])
{
  $REX['PAGES'] = rex_be_navigation::getLoggedInPages();
}

// ----- INCLUDE ADDONS
include_once rex_path::core('/packages.inc.php');

// ----- Prepare AddOn Pages
if($REX['USER'])
{
  foreach(rex_ooAddon::getAvailableAddons() as $addonName)
  {
    $page  = rex_ooAddon::getProperty($addonName, 'page', null);
    $title = rex_ooAddon::getProperty($addonName, 'name', '');
    $href  = rex_ooAddon::getProperty($addonName, 'link',  'index.php?page='. $addonName);
    $perm  = rex_ooAddon::getProperty($addonName, 'perm', '');

    // prepare addons root-page
    $addonPage = null;
    if ($page != null && $page instanceof rex_be_page_container && $page->getPage()->checkPermission($REX['USER']))
    {
        $addonPage = $page;
    }
    else if($perm == '' || $REX['USER']->hasPerm($perm) || $REX['USER']->isAdmin())
    {
      if ($title != '')
      {
        $addonPage = new rex_be_page($title, array('page' => $addonName));
        $addonPage->setHref($href);

        // wegen REX Version = 4.2 - alter Stil "SUBPAGES"
        if(isset($REX['ADDON'][$addonName]['SUBPAGES']))
        {
          $REX['ADDON']['pages'][$addonName] = $REX['ADDON'][$addonName]['SUBPAGES'];
        }
        // *** ENDE wegen <=4.2
      }
    }

    if($addonPage)
    {
      // adds be_page's
      foreach(rex_ooAddon::getProperty($addonName, 'pages', array()) as $s)
      {
        if (is_array($s))
        {
          if (!isset($s[2]) || $REX['USER']->hasPerm($s[2]) || $REX['USER']->isAdmin())
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
    foreach(rex_ooPlugin::getAvailablePlugins($addonName) as $pluginName)
    {
      $page  = rex_ooPlugin::getProperty($addonName, $pluginName, 'page', null);

      $title = rex_ooPlugin::getProperty($addonName, $pluginName, 'name', '');
      $href  = rex_ooPlugin::getProperty($addonName, $pluginName, 'link',  'index.php?page='. $addonName . '&subpage='. $pluginName);
      $perm  = rex_ooPlugin::getProperty($addonName, $pluginName, 'perm', '');

      // prepare plugins root-page
      $pluginPage = null;
      if ($page != null && $page instanceof rex_be_page_container && $page->getPage()->checkPermission($REX['USER']))
      {
          $pluginPage = $page;
      }
      else if ($perm == '' || $REX['USER']->hasPerm($perm) || $REX['USER']->isAdmin())
      {
        if($title != '')
        {
          $pluginPage = new rex_be_page($title, array('page' => $addonName, 'subpage' => $pluginName));
          $pluginPage->setHref($href);
        }
      }

      // add plugin-be_page's to addon
      foreach(rex_ooPlugin::getProperty($addonName, $pluginName, 'pages', array()) as $s)
      {
        if(is_array($s) && $addonPage)
        {
          if (!isset($s[2]) || $REX['USER']->hasPerm($s[2]) || $REX['USER']->isAdmin())
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
          $navProperties = rex_ooPlugin::getProperty($addonName, $pluginName, 'navigation', array());
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
        foreach(rex_ooAddon::getProperty($addonName, 'navigation', array()) as $key => $value)
        {
          $mainAddonPage->_set($key, $value);
        }
        $REX['PAGES'][$addonName] = $mainAddonPage;
      }
    }
  }
}

// Set Startpage
if($REX['USER'])
{
  $REX['USER']->pages = $REX['PAGES'];

  // --- page herausfinden
  $REX['PAGE'] = trim(rex_request('page', 'string'));

  // --- page pruefen und benoetigte rechte checken
  if(!isset($REX['PAGES'][$REX['PAGE']]) ||
    (($p=$REX['PAGES'][$REX['PAGE']]->getPage()) && !$p->checkPermission($REX['USER'])))
  {
    // --- neue page bestimmen und diese in neuem request dann verarbeiten
    $REX['PAGE'] = $REX['LOGIN']->getStartpage();
    if(!isset($REX['PAGES'][$REX['PAGE']]) ||
      (($p=$REX['PAGES'][$REX['PAGE']]->getPage()) && !$p->checkPermission($REX['USER'])))
    {
      $REX['PAGE'] = $REX['START_PAGE'];
      if(!isset($REX['PAGES'][$REX['PAGE']]) ||
        (($p=$REX['PAGES'][$REX['PAGE']]->getPage()) && !$p->checkPermission($REX['USER'])))
      {
        // --- fallback auf "profile"; diese page hat jeder user
        $REX['PAGE'] = 'profile';
      }
    }

    header('Location: index.php?page='. $REX['PAGE']);
    exit();
  }
}

$page = $REX['PAGE'];
$pageObj = $REX['PAGES'][$REX['PAGE']]->getPage();
$REX['PAGE_NO_NAVI'] = !$pageObj->hasNavigation();


// ----- EXTENSION POINT
// page variable validated
rex_register_extension_point( 'PAGE_CHECKED', $REX['PAGE'], array('pages' => $REX['PAGES']));

// trigger api functions
rex_api_function::handleCall();

$path = '';
if($pageObj->hasPath())
{
  // If page has a new/overwritten path
  $path = $pageObj->getPath();
}else if($pageObj->isCorePage())
{
  // Core Page
  $path = rex_path::core('/pages/'. $REX['PAGE'] .'.inc.php');
}else
{
  // Addon Page
  $path = rex_path::addon($REX['PAGE'], 'pages/index.inc.php');
}

if($pageObj->hasLayout())
{
  require rex_path::core('/layout/top.php');
  require $path;
  require rex_path::core('/layout/bottom.php');
}else
{
  require $path;
}

// ----- caching end für output filter
$CONTENT = ob_get_contents();
ob_end_clean();

// ----- inhalt ausgeben
rex_send_article(null, $CONTENT, 'backend', TRUE);