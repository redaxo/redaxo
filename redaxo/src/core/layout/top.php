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

  $safemode = (rex::isSafeMode()) ? '<li><a href="' . rex_url::backendController(array('safemode' => 0)) . '">' . rex_i18n::msg('safemode_deactivate') . '</a></li>' : '';

  $user_name = rex::getUser()->getValue('name') != '' ? rex::getUser()->getValue('name') : rex::getUser()->getValue('login');
  $logout = '<ul>' . $safemode . '<li class="rex-loggedas">' . rex_i18n::msg('logged_in_as') . ' <a href="#">' . htmlspecialchars($user_name) . '</a></li><li><a href="' . rex_url::backendController(array('page' => 'profile')) . '">' . rex_i18n::msg('profile_title') . '</a></li><li><a href="' . rex_url::backendController(array('rex_logout' => 1)) . '"' . rex::getAccesskey(rex_i18n::msg('logout'), 'logout') . '>' . rex_i18n::msg('logout') . '</a></li></ul>';
} elseif ($hasNavigation) {
  $logout = '<ul><li class="rex-loggedas">' . rex_i18n::msg('logged_out') . '</li></ul>';
} else {
  $logout = '<ul><li class="rex-loggedas">&nbsp;</li></ul>';
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

/* Setup Navigation ***********************************************************/
if (rex::getProperty('page') == 'setup') {
  $step = rex_request('step', 'float');
  $lang = rex_request('lang', 'string', '');
  $navi = array();
  for ($i = 1; $i <= 7; $i++) {
    $n = array();
    $n['itemClasses'] = array();
    if ($i == $step)
      $n['itemClasses'][] = 'rex-active';

    if ($i < $step) {
      $n['linkClasses'][] = 'rex-success';
      $n['href'] = rex_url::backendController(array('page' => 'setup', 'step' => $i, 'lang' => $lang));
    }
    $name = '';
    if (isset($n['href']) && $lang != '')
      $name = rex_i18n::msg('setup_' . $i . '99');
    elseif ($lang != '')
      $name = '<span>' . rex_i18n::msg('setup_' . $i . '99') . '</span>';
    elseif ($i == 1)
      $name = '<span>Step 1 / Language</span>';

    $n['title'] = $name;

    $navi[] = $n;
  }
  $block = array();
  $block['headline'] = array('title' => 'Setup');
  $block['navigation'] = $navi;
  $blocks[] = $block;

  $fragment = new rex_fragment();
  // $fragment->setVar('headline', array("title" => $this->getHeadline($block)), false);
  $fragment->setVar('type', 'main', false);
  $fragment->setVar('blocks', $blocks, false);
  $navigation = $fragment->parse('navigation.tpl');
}

/* PJAX Footer Header ***********************************************************/
if (!rex_request::isPJAXContainer('#rex-page')) {
  $fragment = new rex_fragment();
  $fragment->setVar('pageTitle', $page_title);
  $fragment->setVar('pageHeader', rex_extension::registerPoint('PAGE_HEADER', '' ), false);
  $fragment->setVar('bodyAttr', $body, false);
  echo $fragment->parse('backend_top.tpl');
}

$fragment = new rex_fragment();
// $fragment->setVar('pageHeader', rex_extension::registerPoint('PAGE_HEADER', '' ), false);
echo $fragment->parse('backend_header.tpl');

$fragment = new rex_fragment();
$fragment->setVar('logout', $logout, false);
echo $fragment->parse('backend_meta.tpl');

?><section id="rex-page-main" data-pjax-container="#rex-page-main">
