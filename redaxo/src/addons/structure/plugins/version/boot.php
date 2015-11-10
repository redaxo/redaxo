<?php

/**
 * Version.
 *
 * @author jan@kristinus.de
 *
 * @package redaxo5
 */

$mypage = 'version';

rex_perm::register('version[live_version]', null, rex_perm::OPTIONS);

// ***** an EPs andocken
rex_extension::register('ART_INIT', function (rex_extension_point $ep) {
    $version = rex_request('rex_version', 'int');
    if ($version != 1) {
        return;
    }

    rex_login::startSession();

    if (!rex_backend_login::hasSession()) {
        throw new rex_exception('no permission for the working version');
    }

    $article = $ep->getParam('article');
    $article->setSliceRevision($version);
    if ($article instanceof rex_article_content) {
        $article->getContentAsQuery();
    }
    $article->setEval(true);
});

rex_extension::register('STRUCTURE_CONTENT_HEADER', function (rex_extension_point $ep) {
    $params = $ep->getParams();
    $return = '';

    $rex_version_article = rex::getProperty('login')->getSessionVar('rex_version_article');
    if (!is_array($rex_version_article)) {
        $rex_version_article = [];
    }

    $working_version_empty = true;
    $gw = rex_sql::factory();
    $gw->setQuery('select * from ' . rex::getTablePrefix() . 'article_slice where article_id=' . $params['article_id'] . ' and clang_id=' . $params['clang'] . ' and revision=1 LIMIT 1');
    if ($gw->getRows() > 0) {
        $working_version_empty = false;
    }

    $revisions = [];
    $revisions[0] = rex_i18n::msg('version_liveversion');
    $revisions[1] = rex_i18n::msg('version_workingversion');

    $version_id = rex_request('rex_set_version', 'int', '-1');

    if ($version_id === 0) {
        $rex_version_article[$params['article_id']] = 0;
    } elseif ($version_id == 1) {
        $rex_version_article[$params['article_id']] = 1;
    } elseif (!isset($rex_version_article[$params['article_id']])) {
        $rex_version_article[$params['article_id']] = 1;
    }

    $func = rex_request('rex_version_func', 'string');
    switch ($func) {
        case 'copy_work_to_live':
            if ($working_version_empty) {
                $return .= rex_view::error(rex_i18n::msg('version_warning_working_version_to_live'));
            } elseif (rex::getUser()->hasPerm('version[live_version]')) {
                rex_article_revision::copyContent($params['article_id'], $params['clang'], rex_article_revision::WORK, rex_article_revision::LIVE);
                $return .= rex_view::success(rex_i18n::msg('version_info_working_version_to_live'));
            }
        break;
        case 'copy_live_to_work':
            rex_article_revision::copyContent($params['article_id'], $params['clang'], rex_article_revision::LIVE, rex_article_revision::WORK);
            $return .= rex_view::success(rex_i18n::msg('version_info_live_version_to_working'));
        break;
    }

    if (!rex::getUser()->hasPerm('version[live_version]')) {
        $rex_version_article[$params['article_id']] = 1;
        unset($revisions[0]);
    }

    rex::getProperty('login')->setSessionVar('rex_version_article', $rex_version_article);

    $context = new rex_context([
        'page' => $params['page'],
        'article_id' => $params['article_id'],
        'clang' => $params['clang'],
        'ctype' => $params['ctype'],
    ]);

    $items = [];
    $brand = '';
    foreach ($revisions as $version => $revision) {
        $item = [];
        $item['title'] = $revision;
        $item['href'] = $context->getUrl(['rex_set_version' => $version]);
        if ($rex_version_article[$params['article_id']] == $version) {
            $item['active'] = true;
            $brand = $revision;
        }
        $items[] = $item;
    }

    $toolbar = '';

    $fragment = new rex_fragment();
    $fragment->setVar('button_prefix', rex_i18n::msg('version'));
    $fragment->setVar('items', $items, false);
    $fragment->setVar('toolbar', true);

    if (!rex::getUser()->hasPerm('version[live_version]')) {
        $fragment->setVar('disabled', true);
    }

    $toolbar .= '<li class="dropdown">' . $fragment->parse('core/dropdowns/dropdown.php') . '</li>';

    if (!rex::getUser()->hasPerm('version[live_version]')) {
        if ($rex_version_article[$params['article_id']] > 0) {
            $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'copy_live_to_work']) . '">' . rex_i18n::msg('version_copy_from_liveversion') . '</a></li>';
            $toolbar .= '<li><a href="' . rex_getUrl($params['article_id'], $params['clang'], ['rex_version' => 1]) . '" target="_blank">' . rex_i18n::msg('version_preview') . '</a></li>';
        }
    } else {
        if ($rex_version_article[$params['article_id']] > 0) {
            if (!$working_version_empty) {
                $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'copy_work_to_live']) . '">' . rex_i18n::msg('version_working_to_live') . '</a></li>';
            }
            $toolbar .= '<li><a href="' . rex_getUrl($params['article_id'], $params['clang'], ['rex_version' => 1]) . '" target="_blank">' . rex_i18n::msg('version_preview') . '</a></li>';
        } else {
            $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'copy_live_to_work']) . '" data-confirm="' . rex_i18n::msg('version_confirm_copy_live_to_workingversion') . '">' . rex_i18n::msg('version_copy_live_to_workingversion') . '</a></li>';
        }
    }

    $inverse = $rex_version_article[$params['article_id']] == 1 ? true : false;
    $cssClass = $rex_version_article[$params['article_id']] == 1 ? 'rex-state-inprogress' : 'rex-state-live';

    $return .= rex_view::toolbar('<ul class="nav navbar-nav">' . $toolbar . '</ul>', $brand, $cssClass, $inverse);

    $params['slice_revision'] = $rex_version_article[$params['article_id']];

    return $return;
});
