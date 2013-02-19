<?php

/**
 * Layout Kopf des Backends
 * @package redaxo5
 */

$curPage = rex_be_controller::getCurrentPageObject();

if (!$curPage->hasLayout()) {
    if (rex_request::isPJAXRequest()) {
        // add title to the page, so pjax can update it. see gh#136
        echo '<title>' . htmlspecialchars(rex_be_controller::getPageTitle()) . '</title>';
    }
    return;
}

$body_attr = array();
$body_id = str_replace('_', '-', rex_be_controller::getCurrentPage());

if ($curPage->isPopup())
    $body_attr['class'] = array('rex-popup');

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

$hasNavigation = $curPage->hasNavigation();

$meta_items = array();
if (rex::getUser() && $hasNavigation) {

    if (rex::isSafeMode()) {
        $item = array();
        $item['title']  = rex_i18n::msg('safemode_deactivate');
        $item['href']   = rex_url::backendController(array('safemode' => 0));
        $meta_items[] = $item;
        unset($item);
    }


    $user_name = rex::getUser()->getValue('name') != '' ? rex::getUser()->getValue('name') : rex::getUser()->getValue('login');

    $item = array();
    $item['title']  = rex_i18n::msg('logged_in_as') . ' <a class="rex-username" href="' . rex_url::backendPage('profile') . '" title="' . rex_i18n::msg('profile_title') . '">' . htmlspecialchars($user_name) . '</a>';
    $meta_items[] = $item;
    unset($item);

    $item = array();
    $item['title']      = '<span class="rex-icon rex-icon-logout"></span>' . rex_i18n::msg('logout');
    $item['href']       = rex_url::backendController(array('rex_logout' => 1));
    $item['attributes'] = 'class="rex-logout"' . rex::getAccesskey(rex_i18n::msg('logout'), 'logout');
    $meta_items[] = $item;
    unset($item);

} elseif ($hasNavigation) {
    $item = array();
    $item['title']  = rex_i18n::msg('logged_out');
    $meta_items[] = $item;
    unset($item);
}

// wird in bottom.php an Fragment uebergeben
$navigation = '';
if (rex::getUser() && $hasNavigation) {
    $n = rex_be_navigation::factory();
    foreach (rex_be_controller::getPages() as $p => $pageObj) {
        $p = strtolower($p);
        if ($pageObj instanceof rex_be_page_main) {
            $pageObj->setItemAttr('id', 'rex-navi-page-' . strtolower(preg_replace('/[^a-zA-Z0-9\-]*/', '', str_replace('_', '-', $p))));

            if (!$pageObj->getBlock())
                $pageObj->setBlock('addons');

            if (!$pageObj->getHref())
                $pageObj->setHref(rex_url::backendPage($p));
            /*
             if(isset ($REX['ACKEY']['ADDON'][$page]))
                $item['extra'] = rex_accesskey($name, $REX['ACKEY']['ADDON'][$page]);
            else
                $item['extra'] = rex_accesskey($pageArr['title'], $accesskey++);
            */

            $n->addPage($pageObj);
        }
    }

    $blocks = $n->getNavigation();

    $fragment = new rex_fragment();
    // $fragment->setVar('headline', array("title" => $this->getHeadline($block)), false);
    $fragment->setVar('type', 'main', false);
    $fragment->setVar('blocks', $blocks, false);
    $navigation = $fragment->parse('core/navigations/navigation.tpl');
}

/* Setup Navigation ***********************************************************/
if (rex_be_controller::getCurrentPagePart(1) == 'setup') {
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
            $n['href'] = rex_url::backendPage('setup', array('step' => $i, 'lang' => $lang));
            if ($step == 7)
                $n['href'] = 'javascript:void(0)';
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

/* Login Navigation ***********************************************************/
/*
if (!rex::getUser() && !rex::isSetup()) {
    $navi = array();

    $n = array();
    $n['href'] = rex_url::backendPage('login');
    $n['title'] = rex_i18n::msg('login');
    $n['linkClasses'] = array('rex-active');
    $navi[] = $n;

    $block = array();
    $block['headline'] = array('title' => rex_i18n::msg('login'));
    $block['navigation'] = $navi;
    $blocks[] = $block;

    $fragment = new rex_fragment();
    // $fragment->setVar('headline', array("title" => $this->getHeadline($block)), false);
    $fragment->setVar('type', 'main', false);
    $fragment->setVar('blocks', $blocks, false);
    $navigation = $fragment->parse('navigation.tpl');
}
*/
/* PJAX Footer Header ***********************************************************/
if (!rex_request::isPJAXContainer('#rex-page')) {
    $fragment = new rex_fragment();
    $fragment->setVar('pageTitle', rex_be_controller::getPageTitle());
    $fragment->setVar('cssFiles', rex_view::getCssFiles());
    $fragment->setVar('jsFiles', rex_view::getJsFiles());
    $fragment->setVar('jsProperties', json_encode(rex_view::getJsProperties()), false);
    $fragment->setVar('favicon', rex_view::getFavicon());
    $fragment->setVar('pageHeader', rex_extension::registerPoint('PAGE_HEADER', ''), false);
    $fragment->setVar('bodyAttr', $body, false);
    echo $fragment->parse('core/top.tpl');
} elseif (rex_request::isPJAXRequest()) {
    // add title to the page, so pjax can update it. see gh#136
    echo '<title>' . htmlspecialchars(rex_be_controller::getPageTitle()) . '</title>';
}

$fragment = new rex_fragment();
// $fragment->setVar('pageHeader', rex_extension::registerPoint('PAGE_HEADER', '' ), false);
echo $fragment->parse('core/header.tpl');

$fragment = new rex_fragment();
$fragment->setVar('items', $meta_items, false);
echo $fragment->parse('core/meta.tpl');


?><section id="rex-page-main-container"><div id="rex-page-main" data-pjax-container="#rex-page-main">
