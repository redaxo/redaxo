<?php

/**
 * Version.
 *
 * @author jan@kristinus.de
 *
 * @package redaxo5
 */

rex_perm::register('version[live_version]', null, rex_perm::OPTIONS);

// ***** an EPs andocken
rex_extension::register('ART_INIT', static function (rex_extension_point $ep) {
    $version = rex_request(rex_version::class, 'int');
    if (rex_article_revision::WORK != $version) {
        return;
    }

    rex_login::startSession();

    if (!rex_backend_login::hasSession()) {
        throw new rex_exception('No permission for the working version. You need to be logged into the REDAXO backend at the same time.');
    }

    /** @var rex_article_content_base $article */
    $article = $ep->getParam('article');
    $article->setSliceRevision($version);
    if ($article instanceof rex_article_content) {
        $article->getContentAsQuery();
    }
    $article->setEval(true);
});

rex_extension::register('STRUCTURE_CONTENT_HEADER', static function (rex_extension_point $ep) {
    if ('content/edit' !== $ep->getParam('page')) {
        return null;
    }

    $params = $ep->getParams();
    $articleId = rex_type::int($params['article_id']);

    /** @var array<int, 0|1>|null $rexVersionArticle */
    $rexVersionArticle = rex::getProperty('login')->getSessionVar('rex_version_article', []);
    if (!is_array($rexVersionArticle)) {
        $rexVersionArticle = [];
    }

    $versionId = rex_request('rex_set_version', 'int', '-1');

    if (0 === $versionId) {
        $rexVersionArticle[$articleId] = 0;
    } elseif (1 === $versionId) {
        $rexVersionArticle[$articleId] = 1;
    } elseif (!isset($rexVersionArticle[$articleId])) {
        $rexVersionArticle[$articleId] = 1;
    }

    if (!rex::requireUser()->hasPerm('version[live_version]')) {
        $rexVersionArticle[$articleId] = 1;
    }

    rex::getProperty('login')->setSessionVar('rex_version_article', $rexVersionArticle);

    $params['slice_revision'] = $rexVersionArticle[$articleId];
});

rex_extension::register('STRUCTURE_CONTENT_BEFORE_SLICES', static function (rex_extension_point $ep) {
    if ('content/edit' !== $ep->getParam('page')) {
        return null;
    }

    $user = rex::requireUser();
    $params = $ep->getParams();
    $articleId = rex_type::int($params['article_id']);
    $clangId = rex_type::int($params['clang']);
    $return = rex_type::string($ep->getSubject());

    $workingVersionEmpty = true;
    $gw = rex_sql::factory();
    $gw->setQuery('select * from ' . rex::getTablePrefix() . 'article_slice where article_id=? and clang_id=? and revision=1 LIMIT 1', [$articleId, $clangId]);
    if ($gw->getRows() > 0) {
        $workingVersionEmpty = false;
    }

    $func = rex_request('rex_version_func', 'string');
    switch ($func) {
        case 'copy_work_to_live':
            if ($workingVersionEmpty) {
                $return .= rex_view::error(rex_i18n::msg('version_warning_working_version_to_live'));
            } elseif ($user->hasPerm('version[live_version]')) {
                if (rex_plugin::get('structure', 'history')->isAvailable()) {
                    rex_article_slice_history::makeSnapshot($articleId, $clangId, 'work_to_live');
                }

                rex_article_revision::copyContent($articleId, $clangId, rex_article_revision::WORK, rex_article_revision::LIVE);
                $return .= rex_view::success(rex_i18n::msg('version_info_working_version_to_live'));

                $article = rex_type::instanceOf(rex_article::get($articleId, $clangId), rex_article::class);
                $return = rex_extension::registerPoint(new rex_extension_point_art_content_updated($article, 'work_to_live', $return));
            }
        break;
        case 'copy_live_to_work':
            rex_article_revision::copyContent($articleId, $clangId, rex_article_revision::LIVE, rex_article_revision::WORK);
            $return .= rex_view::success(rex_i18n::msg('version_info_live_version_to_working'));

        break;
        case 'clear_work':
            rex_article_revision::clearContent($articleId, $clangId, rex_article_revision::WORK);
            $return .= rex_view::success(rex_i18n::msg('version_info_clear_workingversion'));
        break;
    }

    /** @var array<int, 0|1>|null $rexVersionArticle */
    $rexVersionArticle = rex::getProperty('login')->getSessionVar('rex_version_article');
    if (!is_array($rexVersionArticle)) {
        $rexVersionArticle = [];
    }

    $revisions = [];
    if ($user->hasPerm('version[live_version]')) {
        $revisions[0] = rex_i18n::msg('version_liveversion');
    }
    $revisions[1] = rex_i18n::msg('version_workingversion');

    $context = new rex_context([
        'page' => $params['page'],
        'article_id' => $articleId,
        'clang' => $clangId,
        'ctype' => $params['ctype'],
    ]);

    $items = [];
    $currentRevision = '';
    foreach ($revisions as $version => $revision) {
        $item = [];
        $item['title'] = $revision;
        $item['href'] = $context->getUrl(['rex_set_version' => $version]);
        if ($rexVersionArticle[$articleId] == $version) {
            $item['active'] = true;
            $currentRevision = $revision;
        }
        $items[] = $item;
    }

    $toolbar = '';

    $fragment = new rex_fragment();
    $fragment->setVar('button_prefix', '<b>'.$currentRevision.'</b>', false);
    $fragment->setVar('items', $items, false);
    $fragment->setVar('toolbar', true);

    if (!$user->hasPerm('version[live_version]')) {
        $fragment->setVar('disabled', true);
    }

    $toolbar .= '<li class="dropdown">' . $fragment->parse('core/dropdowns/dropdown.php') . '</li>';

    if (!$user->hasPerm('version[live_version]')) {
        if ($rexVersionArticle[$articleId] > 0) {
            $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'copy_live_to_work']) . '">' . rex_i18n::msg('version_copy_from_liveversion') . '</a></li>';
            $toolbar .= '<li><a href="' . rex_getUrl($articleId, $clangId, [rex_version::class => 1]) . '" rel="noopener noreferrer" target="_blank">' . rex_i18n::msg('version_preview') . '</a></li>';
        }
    } else {
        if ($rexVersionArticle[$articleId] > 0) {
            if (!$workingVersionEmpty) {
                $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'clear_work']) . '" data-confirm="' . rex_i18n::msg('version_confirm_clear_workingversion') . '">' . rex_i18n::msg('version_clear_workingversion') . '</a></li>';
                $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'copy_work_to_live']) . '">' . rex_i18n::msg('version_working_to_live') . '</a></li>';
            }
            $toolbar .= '<li><a href="' . rex_getUrl($articleId, $clangId, [rex_version::class => 1]) . '" rel="noopener noreferrer" target="_blank">' . rex_i18n::msg('version_preview') . '</a></li>';
        } else {
            $toolbar .= '<li><a href="' . $context->getUrl(['rex_version_func' => 'copy_live_to_work']) . '" data-confirm="' . rex_i18n::msg('version_confirm_copy_live_to_workingversion') . '">' . rex_i18n::msg('version_copy_live_to_workingversion') . '</a></li>';
        }
    }

    $inverse = 1 == $rexVersionArticle[$articleId];
    $cssClass = 1 == $rexVersionArticle[$articleId] ? 'rex-state-inprogress' : 'rex-state-live';

    $return .= rex_view::toolbar('<ul class="nav navbar-nav">' . $toolbar . '</ul>', null, $cssClass, $inverse);

    return $return;
});
