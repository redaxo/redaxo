<?php

/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// ----- caching start für output filter
ob_start();
ob_implicit_flush(0);

// ----------------- MAGIC QUOTES CHECK
require './include/functions/function_rex_mquotes.inc.php';

// ----- REX UNSET
unset($REX);

// Flag ob Inhalte mit Redaxo aufgerufen oder
// von der Webseite aus
// Kann wichtig für die Darstellung sein
// Sollte immer true bleiben

$REX['REDAXO'] = true;

// Wenn $REX[GG] = true; dann wird der
// Content aus den redaxo/include/generated/
// genommen

$REX['GG'] = false;

// setzte pfad und includiere klassen und funktionen
$REX['HTDOCS_PATH'] = '../';
require 'include/master.inc.php';

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
  $REX['LANG'] = '';
  $requestLang = rex_request('lang', 'string');
  $langpath = $REX['INCLUDE_PATH'].'/lang';
  $REX['LANGUAGES'] = array();
  if ($handle = opendir($langpath))
  {
    while (false !== ($file = readdir($handle)))
    {
      if (substr($file,-5) == '.lang')
      {
        $locale = substr($file,0,strlen($file)-strlen(substr($file,-5)));
        $REX['LANGUAGES'][] = $locale;
        if($requestLang == $locale)
          $REX['LANG'] = $locale;
      }
    }
  }
  closedir($handle);
  if($REX['LANG'] == '')
    $REX['LANG'] = 'de_de';

  $I18N = rex_create_lang($REX['LANG']);
  
  $REX['PAGES']['setup'] = rex_be_navigation::getSetupPage();
  $REX['PAGE'] = "setup";

}else
{
  // ----------------- CREATE LANG OBJ
  $I18N = rex_create_lang($REX['LANG']);

  // ---- prepare login
  $REX['LOGIN'] = new rex_backend_login($REX['TABLE_PREFIX'] .'user');
  $rex_user_login = rex_post('rex_user_login', 'string');
  $rex_user_psw = rex_post('rex_user_psw', 'string');

  if ($REX['PSWFUNC'] != '')
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
    // Userspezifische Sprache einstellen, falls gleicher Zeichensatz
    $lang = $REX['LOGIN']->getLanguage();
    $I18N_T = rex_create_lang($lang,'',FALSE);
    if ($I18N->msg('htmlcharset') == $I18N_T->msg('htmlcharset')) 
      $I18N = rex_create_lang($lang);

    $REX['USER'] = $REX['LOGIN']->USER;
  }
}

// ----- Prepare Core Pages
if($REX['USER'])
{
  $REX['PAGES'] = rex_be_navigation::getLoggedInPages($REX['USER']);
}

// ----- INCLUDE ADDONS
include_once $REX['INCLUDE_PATH'].'/addons.inc.php';

// ----- Prepare AddOn Pages
if($REX['USER'])
{
  foreach(OOAddon::getAvailableAddons() as $addonName)
  {
    $title = OOAddon::getProperty($addonName, 'name', '');
    $href  = OOAddon::getProperty($addonName, 'link',  'index.php?page='. $addonName);
    $perm  = OOAddon::getProperty($addonName, 'perm', '');
    
    $addonPage = null;
    $mainAddonPage = null;

    if($perm == '' || $REX['USER']->hasPerm($perm) || $REX['USER']->isAdmin())
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
      
      // adds be_page's
      foreach(OOAddon::getProperty($addonName, 'pages', array()) as $s)
      {
        if(is_array($s) && $addonPage)
        {
         $subPage = new rex_be_page($s[1], array('page' => $addonName, 'subpage' => $s[0]));
          $subPage->setHref('index.php?page='.$addonName.'&subpage='.$s[0]);
          $addonPage->addSubPage($subPage);
        }else if(rex_be_main_page::isValid($s))
        {
          $p = $s->getPage();
          $REX['PAGES'][$addonName.'_'.$p->getTitle()] = $s;
        }else if(rex_be_page::isValid($s) && $addonPage)
        {
          $addonPage->addSubPage($s);
        }
      }
    }
    
    // handle plugins
    foreach(OOPlugin::getAvailablePlugins($addonName) as $pluginName)
    {
      $title = OOPlugin::getProperty($addonName, $pluginName, 'name', '');
      $href  = OOPlugin::getProperty($addonName, $pluginName, 'link',  'index.php?page='. $addonName . '&subpage='. $pluginName);
      $perm  = OOPlugin::getProperty($addonName, $pluginName, 'perm', '');
      
      if($perm == '' || $REX['USER']->hasPerm($perm) || $REX['USER']->isAdmin())
      {
        $pluginPage = null;
        if($title != '')
        {
          $pluginPage = new rex_be_page($title, array('page' => $addonName, 'subpage' => $pluginName));
          $pluginPage->setHref($href);
        }
        
        // add plugin-be_page's to addon
        foreach(OOPlugin::getProperty($addonName, $pluginName, 'pages', array()) as $s)
        {
          if(is_array($s) && $addonPage)
          {
            $subPage = new rex_be_page($s[1], array('page' => $addonName, 'subpage' => $s[0]));
            $subPage->setHref('index.php?page='.$addonName.'&subpage='.$s[0]);
            $addonPage->addSubPage($subPage);
          }
          else if(rex_be_main_page::isValid($s))
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
          // "navigation" adds attributes to the plugin-root page
          $navProperties = OOPlugin::getProperty($addonName, $pluginName, 'navigation', array());
          // if there are some navigation attributes set, create a main page and apply attributes to it
          if(count($navProperties) > 0)
          {
            $mainPluginPage = new rex_be_main_page($addonName, $pluginPage);
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

    if ($addonPage) 
    {
      $mainAddonPage = new rex_be_main_page('addons', $addonPage);
      
      // "navigation" adds attributes to the addon-root page
      foreach(OOAddon::getProperty($addonName, 'navigation', array()) as $key => $value)
      {
        $mainAddonPage->_set($key, $value);
      }
      $REX['PAGES'][$addonName] = $mainAddonPage;
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
    if(!isset($REX['PAGES'][$REX['PAGE']]))
    {
      $REX['PAGE'] = $REX['START_PAGE'];
      if(!isset($REX['PAGES'][$REX['PAGE']]))
      {
        // --- fallback auf "profile"; diese page hat jeder user
        $REX['PAGE'] = 'profile';
      }
    }
    
    header('Location: index.php?page='. $REX['PAGE']);
    exit();
  }
}

$page = $REX['PAGES'][$REX['PAGE']]->getPage();
$REX['PAGE_NO_NAVI'] = !$page->hasNavigation();


// ----- EXTENSION POINT
// page variable validated
rex_register_extension_point( 'PAGE_CHECKED', $REX['PAGE'], array('pages' => $REX['PAGES']));


if($page->hasPath())
{
  // If page has a new/overwritten path
  require $page->getPath();

}else if($page->isCorePage())
{
  // Core Page
  require $REX['INCLUDE_PATH'].'/layout/top.php';
  require $REX['INCLUDE_PATH'].'/pages/'. $REX['PAGE'] .'.inc.php';
  require $REX['INCLUDE_PATH'].'/layout/bottom.php';
}else
{
  // Addon Page
  require $REX['INCLUDE_PATH'].'/addons/'. $REX['PAGE'] .'/pages/index.inc.php';
}
// ----- caching end für output filter
$CONTENT = ob_get_contents();
ob_end_clean();

// ----- inhalt ausgeben
rex_send_article(null, $CONTENT, 'backend', TRUE);