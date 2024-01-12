<?php

/**
 * Site Structure Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 */

$addon = rex_addon::get('structure');
$extensions = $addon->getProperty('extensions', []);
$historyIsAvailable = isset($extensions['history']['available']) && $extensions['history']['available'];
$versionIsAvailable = isset($extensions['version']['available']) && $extensions['version']['available'];

rex_perm::register('addArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('addCategory[]', null, rex_perm::OPTIONS);
rex_perm::register('editArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('editCategory[]', null, rex_perm::OPTIONS);
rex_perm::register('deleteArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('deleteCategory[]', null, rex_perm::OPTIONS);
rex_perm::register('moveArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('moveCategory[]', null, rex_perm::OPTIONS);
rex_perm::register('copyArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('copyContent[]', null, rex_perm::OPTIONS);
rex_perm::register('publishArticle[]', null, rex_perm::OPTIONS);
rex_perm::register('publishCategory[]', null, rex_perm::OPTIONS);
rex_perm::register('article2startarticle[]', null, rex_perm::OPTIONS);
rex_perm::register('article2category[]', null, rex_perm::OPTIONS);

rex_complex_perm::register('structure', rex_structure_perm::class);

require_once __DIR__ . '/functions/function_rex_url.php';

$addon->setProperty('start_article_id', $addon->getConfig('start_article_id', 1));
$addon->setProperty('notfound_article_id', $addon->getConfig('notfound_article_id', 1));

if (0 == rex_request('article_id', 'int')) {
    $addon->setProperty('article_id', rex_article::getSiteStartArticleId());
} else {
    $articleId = rex_request('article_id', 'int');
    $articleId = rex_article::get($articleId) ? $articleId : rex_article::getNotfoundArticleId();
    $addon->setProperty('article_id', $articleId);
}

if (rex::isBackend() && rex::getUser()) {
    rex_view::addJsFile($addon->getAssetsUrl('linkmap.js'), [rex_view::JS_IMMUTABLE => true]);

    if ('system' == rex_be_controller::getCurrentPagePart(1)) {
        rex_system_setting::register(new rex_system_setting_article_id('start_article_id'));
        rex_system_setting::register(new rex_system_setting_article_id('notfound_article_id'));
    }
}

rex_extension::register('CLANG_ADDED', static function (rex_extension_point $ep) {
    $firstLang = rex_sql::factory();
    $firstLang->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang_id=?', [rex_clang::getStartId()]);
    $fields = $firstLang->getFieldnames();

    $newLang = rex_sql::factory();
    // $newLang->setDebug();
    foreach ($firstLang as $firstLangArt) {
        $newLang->setTable(rex::getTablePrefix() . 'article');

        foreach ($fields as $value) {
            if ('pid' == $value) {
                echo '';
            } // nix passiert
            elseif ('clang_id' == $value) {
                $newLang->setValue('clang_id', $ep->getParam('clang')->getId());
            } elseif ('status' == $value) {
                $newLang->setValue('status', '0');
            } // Alle neuen Artikel offline
            else {
                $newLang->setValue($value, $firstLangArt->getValue($value));
            }
        }

        $newLang->insert();
    }
});

rex_extension::register('CLANG_DELETED', static function (rex_extension_point $ep) {
    $del = rex_sql::factory();
    $del->setQuery('delete from ' . rex::getTablePrefix() . 'article where clang_id=?', [$ep->getParam('clang')->getId()]);
});

rex_extension::register('CACHE_DELETED', static function () {
    rex_structure_element::clearInstancePool();
    rex_structure_element::clearInstanceListPool();
    rex_structure_element::resetClassVars();
});

// Version extension
if ($versionIsAvailable) {
    rex_perm::register('version[live_version]', null, rex_perm::OPTIONS);

    // ***** an EPs andocken
    rex_extension::register('ART_INIT', static function (rex_extension_point $ep) {
        $version = rex_request('rex_version', 'int');
        if (rex_article_revision::WORK != $version) {
            return;
        }

        if (!rex_backend_login::hasSession()) {
            $fragment = new rex_fragment([
                'content' => '<p>No permission for the working version. You need to be logged into the REDAXO backend at the same time.</p>',
            ]);
            rex_response::setStatus(rex_response::HTTP_UNAUTHORIZED);
            rex_response::sendPage($fragment->parse('core/fe_ooops.php'));
            exit;
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

        $version = rex_article_revision::getSessionArticleRevision($articleId);
        $newVersion = rex_request('rex_set_version', 'int', null);

        if (rex_article_revision::LIVE === $newVersion) {
            $version = rex_article_revision::LIVE;
        } elseif (rex_article_revision::WORK === $newVersion) {
            $version = rex_article_revision::WORK;
        }

        if (!rex::requireUser()->hasPerm('version[live_version]')) {
            $version = rex_article_revision::WORK;
        }

        rex_article_revision::setSessionArticleRevision($articleId, $version);

        $params['slice_revision'] = $version;
    });

    rex_extension::register('STRUCTURE_CONTENT_BEFORE_SLICES', static function (rex_extension_point $ep) use ($historyIsAvailable) {
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
        $gw->setQuery(
            'select * from '.rex::getTablePrefix(
            ).'article_slice where article_id=? and clang_id=? and revision=1 LIMIT 1',
            [$articleId, $clangId]
        );
        if ($gw->getRows() > 0) {
            $workingVersionEmpty = false;
        }

        $func = rex_request('rex_version_func', 'string');
        switch ($func) {
            case 'copy_work_to_live':
                if ($workingVersionEmpty) {
                    $return .= rex_view::error(rex_i18n::msg('version_warning_working_version_to_live'));
                } elseif ($user->hasPerm('version[live_version]')) {
                    if ($historyIsAvailable) {
                        rex_article_slice_history::makeSnapshot($articleId, $clangId, 'work_to_live');
                    }

                    rex_article_revision::copyContent(
                        $articleId,
                        $clangId,
                        rex_article_revision::WORK,
                        rex_article_revision::LIVE
                    );
                    $return .= rex_view::success(rex_i18n::msg('version_info_working_version_to_live'));

                    $article = rex_type::instanceOf(rex_article::get($articleId, $clangId), rex_article::class);
                    rex_article_revision::setSessionArticleRevision($articleId, rex_article_revision::LIVE);
                    $return = rex_extension::registerPoint(
                        new rex_extension_point_art_content_updated($article, 'work_to_live', $return)
                    );
                }
                break;
            case 'copy_live_to_work':
                rex_article_revision::copyContent(
                    $articleId,
                    $clangId,
                    rex_article_revision::LIVE,
                    rex_article_revision::WORK
                );
                $return .= rex_view::success(rex_i18n::msg('version_info_live_version_to_working'));
                rex_article_revision::setSessionArticleRevision($articleId, rex_article_revision::WORK);
                break;
            case 'clear_work':
                rex_article_revision::clearContent($articleId, $clangId, rex_article_revision::WORK);
                $return .= rex_view::success(rex_i18n::msg('version_info_clear_workingversion'));
                break;
        }

        $revision = rex_article_revision::getSessionArticleRevision($articleId);

        $revisions = [];
        if ($user->hasPerm('version[live_version]')) {
            $revisions[rex_article_revision::LIVE] = rex_i18n::msg('version_liveversion');
        }
        $revisions[rex_article_revision::WORK] = rex_i18n::msg('version_workingversion');

        $context = new rex_context([
            'page' => $params['page'],
            'article_id' => $articleId,
            'clang' => $clangId,
            'ctype' => $params['ctype'],
        ]);

        $items = [];
        $currentRevision = '';
        foreach ($revisions as $version => $label) {
            $item = [];
            $item['title'] = $label;
            $item['href'] = $context->getUrl(['rex_set_version' => $version]);
            if ($revision == $version) {
                $item['active'] = true;
                $currentRevision = $label;
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

        $toolbar .= '<li class="dropdown">'.$fragment->parse('core/dropdowns/dropdown.php').'</li>';

        if (!$user->hasPerm('version[live_version]')) {
            if ($revision > 0) {
                $toolbar .= '<li><a href="'.$context->getUrl(['rex_version_func' => 'copy_live_to_work']
                    ).'">'.rex_i18n::msg('version_copy_from_liveversion').'</a></li>';
                $toolbar .= '<li><a href="'.rex_getUrl(
                        $articleId,
                        $clangId,
                        ['rex_version' => rex_article_revision::WORK]
                    ).'" rel="noopener noreferrer" target="_blank">'.rex_i18n::msg('version_preview').'</a></li>';
            }
        } else {
            if ($revision > 0) {
                if (!$workingVersionEmpty) {
                    $toolbar .= '<li><a href="'.$context->getUrl(['rex_version_func' => 'clear_work']
                        ).'" data-confirm="'.rex_i18n::msg('version_confirm_clear_workingversion').'">'.rex_i18n::msg(
                            'version_clear_workingversion'
                        ).'</a></li>';
                    $toolbar .= '<li><a href="'.$context->getUrl(['rex_version_func' => 'copy_work_to_live']
                        ).'">'.rex_i18n::msg('version_working_to_live').'</a></li>';
                }
                $toolbar .= '<li><a href="'.rex_getUrl(
                        $articleId,
                        $clangId,
                        ['rex_version' => rex_article_revision::WORK]
                    ).'" rel="noopener noreferrer" target="_blank">'.rex_i18n::msg('version_preview').'</a></li>';
            } else {
                $toolbar .= '<li><a href="'.$context->getUrl(['rex_version_func' => 'copy_live_to_work']
                    ).'" data-confirm="'.rex_i18n::msg(
                        'version_confirm_copy_live_to_workingversion'
                    ).'">'.rex_i18n::msg('version_copy_live_to_workingversion').'</a></li>';
            }
        }

        $inverse = rex_article_revision::WORK == $revision;
        $cssClass = rex_article_revision::WORK == $revision ? 'rex-state-inprogress' : 'rex-state-live';

        $return .= rex_view::toolbar('<ul class="nav navbar-nav">'.$toolbar.'</ul>', null, $cssClass, $inverse);

        return $return;
    });
}
