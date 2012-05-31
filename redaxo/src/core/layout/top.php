<?php

/**
 * Layout Kopf des Backends
 * @package redaxo5
 */

$popups_arr = array('linkmap', 'mediapool');

$page_title = rex::getProperty('servername');

if (!isset($page_name)) {
  $pages = rex::getProperty('pages');
  $curPage = $pages[rex::getProperty('page')]->getPage();
  $page_name = $curPage->getTitle();
}

if ($page_name != '')
  $page_title .= ' - ' . $page_name;

$body_attr = array();
$body_id = str_replace('_', '-', rex::getProperty('page'));

if (in_array($body_id, $popups_arr))
  $body_attr['class'] = array('rex-popup' . $body_id);

$body_attr['id'] = array('rex-page-' . $body_id);
$body_attr['onunload'] = array('closeAll();');

// ----- EXTENSION POINT
$body_attr = rex_extension::registerPoint('PAGE_BODY_ATTR', $body_attr );

$body = '';
foreach ($body_attr as $k => $v) {
  $body .= ' ' . $k . '="';
  if (is_array($v))
    $body .= implode(' ', $v);
  $body .= '"';
}

$activePageObj = $curPage;
if ($subpage = $curPage->getActiveSubPage()) {
  $activePageObj = $subpage;
}
$hasNavigation = $activePageObj->hasNavigation();

$logout = '';
if (rex::getUser() && $hasNavigation) {
  $accesskey = 1;
  $user_name = rex::getUser()->getValue('name') != '' ? rex::getUser()->getValue('name') : rex::getUser()->getValue('login');
  $logout = '<ul class="rex-logout"><li class="rex-first"><span>' . rex_i18n::msg('logged_in_as') . ' ' . htmlspecialchars($user_name) . '</span></li><li><a href="index.php?page=profile">' . rex_i18n::msg('profile_title') . '</a></li><li><a href="index.php?rex_logout=1"' . rex::getAccesskey(rex_i18n::msg('logout'), 'logout') . '>' . rex_i18n::msg('logout') . '</a></li></ul>' . "\n";
} elseif ($hasNavigation) {
  $logout = '<p class="rex-logout">' . rex_i18n::msg('logged_out') . '</p>';
} else {
  $logout = '<p class="rex-logout">&nbsp;</p>';
}


$navigation = '';
if (rex::getUser() && $hasNavigation) {
  $n = rex_be_navigation::factory();
  foreach (rex::getProperty('pages') as $p => $pageContainer) {
    $p = strtolower($p);
    if (rex_be_page_main::isValid($pageContainer)) {
      $pageObj = $pageContainer->getPage();
      $pageObj->setItemAttr('id', 'rex-navi-page-' . strtolower(preg_replace('/[^a-zA-Z0-9\-_]*/', '', $p)));

      if (!$pageContainer->getBlock())
        $pageContainer->setBlock('addons');

      if (!$pageObj->getHref())
        $pageObj->setHref('index.php?page=' . $p);
      /*
       if(isset ($REX['ACKEY']['ADDON'][$page]))
        $item['extra'] = rex_accesskey($name, $REX['ACKEY']['ADDON'][$page]);
      else
        $item['extra'] = rex_accesskey($pageArr['title'], $accesskey++);
      */

      $n->addPage($pageContainer);
    }
  }

  $n->setActiveElements();
  $blocks = $n->getNavigation();

  $fragment = new rex_fragment();
  // $fragment->setVar('headline', array("title" => $this->getHeadline($block)), false);
  $fragment->setVar('type', 'main', false);
  $fragment->setVar('blocks', $blocks, false);
  $navigation = $fragment->parse('navigation.tpl');


}




$fragment = new rex_fragment();
$fragment->setVar('pageTitle', $page_title);
$fragment->setVar('pageHeader', rex_extension::registerPoint('PAGE_HEADER', '' ), false);
$fragment->setVar('bodyAttr', $body, false);
echo $fragment->parse('backend_top.tpl');

$fragment = new rex_fragment();
// $fragment->setVar('pageHeader', rex_extension::registerPoint('PAGE_HEADER', '' ), false);
echo $fragment->parse('backend_header.tpl');

$fragment = new rex_fragment();
$fragment->setVar('logout', $logout, false);
echo $fragment->parse('backend_meta.tpl');

?><section id="rex-page-main">
