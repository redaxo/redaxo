<?php

/**
 * Layout Kopf des Backends
 * @package redaxo4
 * @version svn:$Id$
 */
 
$popups_arr = array('linkmap', 'mediapool');

$page_title = $REX['SERVERNAME'];

if(!isset($page_name))
{
  $curPage = $REX['PAGES'][$REX['PAGE']]->getPage();
  $page_name = $curPage->getTitle();
}
  
if ($page_name != '')
  $page_title .= ' - ' . $page_name;

$body_attr = array();
$body_id = str_replace('_', '-', $REX["PAGE"]);

if (in_array($body_id, $popups_arr))
  $body_attr["class"] = array('rex-popup'.$body_id);

$body_attr["id"] = array('rex-page-'.$body_id);
$body_attr["onunload"] = array('closeAll();');

// ----- EXTENSION POINT
$body_attr = rex_register_extension_point('PAGE_BODY_ATTR', $body_attr );

$body = "";
foreach($body_attr as $k => $v){
	$body .= $k.'="';
	if(is_array($v))
		$body .= implode(" ",$v);
	$body .= '" ';
}

$logout = '';
if ($REX['USER'] && !$REX["PAGE_NO_NAVI"])
{
  $accesskey = 1;
  $user_name = $REX['USER']->getValue('name') != '' ? $REX['USER']->getValue('name') : $REX['USER']->getValue('login');
  $logout = '<ul class="rex-logout"><li class="rex-first"><span>' . rex_i18n::msg('logged_in_as') . ' '. htmlspecialchars($user_name) .'</span></li><li><a href="index.php?page=profile">' . rex_i18n::msg('profile_title') . '</a></li><li><a href="index.php?rex_logout=1"'. rex_accesskey(rex_i18n::msg('logout'), $REX['ACKEY']['LOGOUT']) .'>' . rex_i18n::msg('logout') . '</a></li></ul>' . "\n";
}else if(!$REX["PAGE_NO_NAVI"])
{
  $logout = '<p class="rex-logout">' . rex_i18n::msg('logged_out') . '</p>';
}else
{
  $logout = '<p class="rex-logout">&nbsp;</p>';
}
  

$navigation = '';
if ($REX['USER'] && !$REX["PAGE_NO_NAVI"])
{
	$n = rex_be_navigation::factory();
	foreach($REX['USER']->pages as $p => $pageContainer)
  {
		$p = strtolower($p);
    if(rex_be_page_main::isValid($pageContainer))
    {
      $pageObj = $pageContainer->getPage();
      $pageObj->setItemAttr('id', 'rex-navi-page-'.strtolower(preg_replace('/[^a-zA-Z0-9\-_]*/', '', $p)));
      
      if(!$pageContainer->getBlock())
        $pageContainer->setBlock('addons');
        
      if(!$pageObj->getHref())
        $pageObj->setHref('index.php?page='.$p);
      /*
       if(isset ($REX['ACKEY']['ADDON'][$page]))
        $item['extra'] = rex_accesskey($name, $REX['ACKEY']['ADDON'][$page]);
      else 
        $item['extra'] = rex_accesskey($pageArr['title'], $accesskey++);
      */
        
      $pageObj->setLinkAttr('tabindex', rex_tabindex(false));
      $n->addPage($pageContainer);
    }
  }
	
  $n->setActiveElements();
  $navigation = $n->getNavigation();
}

$topfragment = new rex_fragment();
$topfragment->setVar('pageTitle', $page_title);
$topfragment->setVar('pageHeader', rex_register_extension_point('PAGE_HEADER', '' ), false);
$topfragment->setVar('bodyAttr', $body, false);
$topfragment->setVar('logout', $logout, false);
$topfragment->setVar('navigation', $navigation, false);
echo $topfragment->parse('core_top');
unset($topfragment);