<?php

/**
 * Layout Kopf des Backends.
 *
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

$body_attr = [];
$body_id = rex_string::normalize(rex_be_controller::getCurrentPage(), '-', ' ');

$body_attr['id'] = ['rex-page-' . $body_id];
$body_attr['onunload'] = ['closeAll();'];

$body_attr['class'] = ['rex-is-logged-out'];
if (rex::getUser()) {
    $body_attr['class'] = ['rex-is-logged-in'];
}
if ($curPage->isPopup()) {
    $body_attr['class'][] = 'rex-is-popup';
}
// ----- EXTENSION POINT
$body_attr = rex_extension::registerPoint(new rex_extension_point('PAGE_BODY_ATTR', $body_attr));

$body = '';
foreach ($body_attr as $k => $v) {
    $body .= ' ' . $k . '="';
    if (is_array($v)) {
        $body .= implode(' ', $v);
    }
    $body .= '"';
}

$hasNavigation = $curPage->hasNavigation();

$meta_items = [];
if (rex::getUser() && $hasNavigation) {
    if (rex::isSafeMode()) {
        $item = [];
        $item['title'] = rex_i18n::msg('safemode_deactivate');
        $item['href'] = rex_url::backendController(['safemode' => 0]);
        $meta_items[] = $item;
        unset($item);
    }

    $user_name = rex::getUser()->getValue('name') != '' ? rex::getUser()->getValue('name') : rex::getUser()->getValue('login');

    $item = [];
    $item['title'] = '<span class="text-muted">' . rex_i18n::msg('logged_in_as') . '</span> <a class="rex-username" href="' . rex_url::backendPage('profile') . '" title="' . rex_i18n::msg('profile_title') . '"><i class="rex-icon rex-icon-user"></i> ' . htmlspecialchars($user_name) . '</a>';
    $meta_items[] = $item;
    unset($item);

    $item = [];
    $item['title'] = '<i class="rex-icon rex-icon-sign-out"></i> ' . rex_i18n::msg('logout');
    $item['href'] = rex_url::backendController(['rex_logout' => 1]);
    $item['attributes'] = 'class="rex-logout"' . rex::getAccesskey(rex_i18n::msg('logout'), 'logout');
    $meta_items[] = $item;
    unset($item);
} elseif ($hasNavigation) {
    $item = [];
    $item['title'] = rex_i18n::msg('logged_out');
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

            if (!$pageObj->getBlock()) {
                $pageObj->setBlock('addons');
            }

            if (!$pageObj->getHref()) {
                $pageObj->setHref(rex_url::backendPage($p, [], false));
            }
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

    $navigation = '';
    foreach ($blocks as $block) {
        $fragment = new rex_fragment();
        $fragment->setVar('headline', $block['headline'], false);
        $fragment->setVar('items', $block['navigation'], false);
        $navigation .= $fragment->parse('core/navigations/main.php');
    }
}

/* Setup Navigation ***********************************************************/
if (rex_be_controller::getCurrentPagePart(1) == 'setup') {
    $step = rex_request('step', 'float');
    $lang = rex_request('lang', 'string', '');
    $navi = [];
    $end = $lang ? 7 : 1;
    for ($i = 1; $i <= $end; ++$i) {
        $n = [];
        if ($i == $step) {
            $n['active'] = true;
        }

        $n['href'] = 'javascript:void(0)';
        if ($i < $step) {
            $n['itemAttr']['class'][] = 'bg-success';
            $n['href'] = rex_url::backendPage('setup', ['step' => $i, 'lang' => $lang]);
            if ($step == 7) {
                $n['href'] = 'javascript:void(0)';
            }
        }
        $name = '';
        if (isset($n['href']) && $lang != '') {
            $name = rex_i18n::msg('setup_' . $i . '99');
        } elseif ($lang != '') {
            $name = '<span>' . rex_i18n::msg('setup_' . $i . '99') . '</span>';
        } elseif ($i == 1) {
            $name = '<span>Step 1 / Language</span>';
        }

        $n['title'] = $name;

        $navi[] = $n;
    }

    $fragment = new rex_fragment();
    $fragment->setVar('headline', ['title' => 'Setup'], false);
    $fragment->setVar('items', $navi, false);
    $navigation = $fragment->parse('core/navigations/main.php');
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
    $navigation = $fragment->parse('navigation.php');
}
*/
/* PJAX Footer Header ***********************************************************/
if (!rex_request::isPJAXContainer('#rex-js-page-container')) {
    $fragment = new rex_fragment();
    $fragment->setVar('pageTitle', rex_be_controller::getPageTitle());
    $fragment->setVar('cssFiles', rex_view::getCssFiles());
    $fragment->setVar('jsFiles', rex_view::getJsFiles());
    $fragment->setVar('jsProperties', json_encode(rex_view::getJsProperties()), false);
    $fragment->setVar('favicon', rex_view::getFavicon());
    $fragment->setVar('pageHeader', rex_extension::registerPoint(new rex_extension_point('PAGE_HEADER', '')), false);
    $fragment->setVar('bodyAttr', $body, false);
    echo $fragment->parse('core/top.php');

    $fragment = new rex_fragment();
    $fragment->setVar('items', $meta_items, false);
    $meta_navigation = $fragment->parse('core/navigations/meta.php');

    $fragment = new rex_fragment();
    // $fragment->setVar('pageHeader', rex_extension::registerPoint(new rex_extension_point('PAGE_HEADER', '')), false);
    $fragment->setVar('meta_navigation', $meta_navigation, false);
    echo $fragment->parse('core/header.php');

    echo '<div id="rex-js-page-container" class="rex-page-container">';
} elseif (rex_request::isPJAXRequest()) {
    // add title to the page, so pjax can update it. see gh#136
    echo '<title>' . htmlspecialchars(rex_be_controller::getPageTitle()) . '</title>';
}

$fragment = new rex_fragment();
$fragment->setVar('navigation', $navigation, false);
echo $fragment->parse('core/navigation.php');

$pjax = $curPage->allowsPjax() ? ' data-pjax-container="#rex-js-page-main"' : '';

?><div class="rex-page-main"><section class="rex-page-main-inner" id="rex-js-page-main"<?= $pjax ?>>
