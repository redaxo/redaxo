<?php

/**
 * Site Structure Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 */

$addon = rex_addon::get('structure');

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
        rex_system_setting::register(new rex_system_setting_default_template_id());
        rex_system_setting::register(new rex_system_setting_package_status('history'));
        rex_system_setting::register(new rex_system_setting_package_status('version'));
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


/**
 * Content
 */
rex_perm::register('moveSlice[]', null, rex_perm::OPTIONS);
rex_perm::register('publishSlice[]', null, rex_perm::OPTIONS);
rex_complex_perm::register('modules', rex_module_perm::class);

if (rex::isBackend()) {
    rex_extension::register('PAGE_CHECKED', static function () {
        if ('content' == rex_be_controller::getCurrentPagePart(1)) {
            rex_be_controller::getPageObject('structure')->setIsActive(true);
        }
    });

    if ('content' == rex_be_controller::getCurrentPagePart(1)) {
        rex_view::addJsFile(rex_url::addonAssets('structure', 'content.js'), [rex_view::JS_IMMUTABLE => true]);
    }

    rex_extension::register('CLANG_DELETED', static function (rex_extension_point $ep) {
        $del = rex_sql::factory();
        $del->setQuery('delete from ' . rex::getTablePrefix() . 'article_slice where clang_id=?', [$ep->getParam('clang')->getId()]);
    });
} else {
    rex_extension::register('FE_OUTPUT', static function (rex_extension_point $ep) {
        $clangId = rex_get('clang', 'int');
        if ($clangId && !rex_clang::exists($clangId)) {
            rex_redirect(rex_article::getNotfoundArticleId(), rex_clang::getStartId());
        }

        $content = $ep->getSubject();

        $article = new rex_article_content();
        $article->setCLang(rex_clang::getCurrentId());

        if (!$article->setArticleId(rex_article::getCurrentId())) {
            if (!rex::isDebugMode() && !rex_backend_login::hasSession()) {
                throw new rex_exception('Article with id ' . rex_article::getCurrentId() . ' does not exist');
            }

            $fragment = new rex_fragment([
                'content' => '<p><b>Article with ID ' . rex_article::getCurrentId() . ' not found.</b><br />If this is a fresh setup, an article must be created first.<br />Enter <a href="' . rex_url::backendController() . '">REDAXO</a>.</p>',
            ]);
            $content .= $fragment->parse('core/fe_ooops.php');
            rex_response::sendPage($content);
            exit;
        }

        try {
            $content .= $article->getArticleTemplate();
        } catch (rex_article_not_found_exception) {
            $article = new rex_article_content();
            $article->setCLang(rex_clang::getCurrentId());
            $article->setArticleId(rex_article::getNotfoundArticleId());

            $content .= $article->getArticleTemplate();
        }

        $artId = $article->getArticleId();
        if ($artId == rex_article::getNotfoundArticleId() && $artId != rex_article::getSiteStartArticleId()) {
            rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
        }

        // ----- inhalt ausgeben
        rex_response::sendPage($content, $article->getValue('updatedate'));
    });
}

rex_extension::register('EDITOR_URL', static function (rex_extension_point $ep) {
    $urls = [
        'template' => ['templates', 'template_id'],
        'module' => ['modules/modules', 'module_id'],
        'action' => ['modules/actions', 'action_id'],
    ];

    if (preg_match('@^rex:///(template|module|action)/(\d+)@', $ep->getParam('file'), $match)) {
        return rex_url::backendPage($urls[$match[1]][0], ['function' => 'edit', $urls[$match[1]][1] => $match[2]]);
    }

    return null;
});



/**
 * History
 */
if (true === $addon->getConfig('history', false)) {
    rex_extension::register('PAGE_CHECKED', static function (rex_extension_point $ep) {
        $page = rex_be_controller::getPageObject('content');
        if ($page && $historyPage = $page->getSubpage('history')) {
            $historyPage->setHidden(false);
        }
        $page = rex_be_controller::getPageObject('system');
        if ($page && $historyPage = $page->getSubpage('history')) {
            $historyPage->setHidden(false);
        }
    });
    $historyDate = rex_request('rex_history_date', 'string');

    rex_perm::register('history[article_rollback]', null, rex_perm::OPTIONS);

    if ('' != $historyDate) {
        $historySession = rex_request('rex_history_session', 'string');
        $historyLogin = rex_request('rex_history_login', 'string');
        $historyValidtime = rex_request('rex_history_validtime', 'string');

        $user = null;
        if ('' != $historySession && '' != $historyLogin && '' != $historyValidtime && !rex::isBackend()) {
            $validtill = DateTime::createFromFormat('YmdHis', $historyValidtime);
            $now = new DateTime();
            if ($now < $validtill) {
                $login = new rex_history_login();

                if ($login->checkTempSession($historyLogin, $historySession, $historyValidtime)) {
                    $user = $login->getUser();
                    rex::setProperty('user', $user);
                    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) use ($login) {
                        $login->deleteSession();
                    });
                }
            }
        } else {
            $user = rex_backend_login::createUser();
        }

        if (!$user) {
            throw new rex_exception('no permission');
        }

        if (!$user->hasPerm('history[article_rollback]')) {
            throw new rex_exception('no permission for the slice version');
        }

        rex_extension::register('ART_INIT', static function (rex_extension_point $ep) {
            $article = $ep->getParam('article');
            if ($article instanceof rex_article_content) {
                $article->getContentAsQuery();
            }
            $article->setEval(true);
        });

        rex_extension::register('ART_SLICES_QUERY', static function (rex_extension_point $ep) {
            $historyDate = rex_request('rex_history_date', 'string');
            $article = $ep->getParam('article');

            if ($article instanceof rex_article_content && $article->getArticleId() == rex_article::getCurrentId()) {
                $articleLimit = '';
                if (0 != $article->getArticleId()) {
                    $articleLimit = ' AND '.rex::getTablePrefix().'article_slice.article_id='.$article->getArticleId();
                }

                $sliceLimit = '';

                rex_article_slice_history::checkTables();

                $escapeSql = rex_sql::factory();

                $sliceDate = ' AND '.rex::getTablePrefix().'article_slice.history_date = '.$escapeSql->escape(
                        $historyDate
                    );

                return 'SELECT '.rex::getTablePrefix().'module.id, '.rex::getTablePrefix(
                    ).'module.key,'.rex::getTablePrefix().'module.name, '.rex::getTablePrefix(
                    ).'module.output, '.rex::getTablePrefix().'module.input, '.rex::getTablePrefix(
                    ).'article_slice.*, '.rex::getTablePrefix().'article.parent_id
                    FROM
                        '.rex_article_slice_history::getTable().' as '.rex::getTablePrefix().'article_slice
                    LEFT JOIN '.rex::getTablePrefix().'module ON '.rex::getTablePrefix(
                    ).'article_slice.module_id='.rex::getTablePrefix().'module.id
                    LEFT JOIN '.rex::getTablePrefix().'article ON '.rex::getTablePrefix(
                    ).'article_slice.article_id='.rex::getTablePrefix().'article.id
                    WHERE
                        '.rex::getTablePrefix()."article_slice.clang_id='".$article->getClangId()."' AND
                        ".rex::getTablePrefix()."article.clang_id='".$article->getClangId()."' AND
                        ".rex::getTablePrefix().'article_slice.revision=0
                        '.$articleLimit.'
                        '.$sliceLimit.'
                        '.$sliceDate.'
                        ORDER BY '.rex::getTablePrefix().'article_slice.priority';
            }

            return null;
        });
    }

    if (rex::isBackend() && rex::getUser()?->hasPerm('history[article_rollback]')) {
        rex_extension::register(
            ['ART_SLICES_COPY', 'SLICE_ADD', 'SLICE_UPDATE', 'SLICE_MOVE', 'SLICE_DELETE'],
            static function (rex_extension_point $ep) {
                $type = match ($ep->getName()) {
                    'ART_SLICES_COPY' => 'slices_copy',
                    'SLICE_MOVE' => 'slice_'.$ep->getParam('direction'),
                    default => strtolower($ep->getName()),
                };

                $articleId = $ep->getParam('article_id');
                $clangId = $ep->getParam('clang_id');
                $sliceRevision = $ep->getParam('slice_revision');

                if (0 == $sliceRevision) {
                    rex_article_slice_history::makeSnapshot($articleId, $clangId, $type);
                }
            },
        );

        rex_view::addCssFile($addon->getAssetsUrl('noUiSlider/nouislider.css'));
        rex_view::addJsFile($addon->getAssetsUrl('noUiSlider/nouislider.js'), [rex_view::JS_IMMUTABLE => true]);
        rex_view::addCssFile($addon->getAssetsUrl('history.css'));
        rex_view::addJsFile($addon->getAssetsUrl('history.js'), [rex_view::JS_IMMUTABLE => true]);

        switch (rex_request('rex_history_function', 'string')) {
            case 'snap':
                $articleId = rex_request('history_article_id', 'int');
                $clangId = rex_request('history_clang_id', 'int');
                $historyDate = rex_request('history_date', 'string');
                rex_article_slice_history::restoreSnapshot($historyDate, $articleId, $clangId);

            // no break
            case 'layer':
                $articleId = rex_request('history_article_id', 'int');
                $clangId = rex_request('history_clang_id', 'int');
                $versions = rex_article_slice_history::getSnapshots($articleId, $clangId);

                $select1 = [];
                $select1[] = '<option value="0" selected="selected" data-revision="0">'.$addon->i18n(
                        'structure_history_current_version'
                    ).'</option>';
                if (true === $addon->getConfig('version', false)) {
                    $select1[] = '<option value="1" data-revision="1">'.rex_i18n::msg(
                            'version_workingversion'
                        ).'</option>';
                }

                $select2 = [];
                $select2[] = '<option value="" selected="selected">'.$addon->i18n(
                        'structure_history_current_version'
                    ).'</option>';
                foreach ($versions as $version) {
                    $historyInfo = $version['history_date'];
                    if ('' != $version['history_user']) {
                        $historyInfo = $version['history_date'].' ['.$version['history_user'].']';
                    }
                    $select2[] = '<option value="'.strtotime(
                            $version['history_date']
                        ).'" data-history-date="'.rex_escape(
                            $version['history_date']
                        ).'">'.rex_escape($historyInfo).'</option>';
                }

                $content1select = '<select id="content-history-select-date-1" class="content-history-select" data-iframe="content-history-iframe-1" style="">'.implode(
                        '',
                        $select1
                    ).'</select>';
                $content1iframe = '<iframe id="content-history-iframe-1" class="history-iframe"></iframe>';
                $content2select = '<select id="content-history-select-date-2" class="content-history-select" data-iframe="content-history-iframe-2">'.implode(
                        '',
                        $select2
                    ).'</select>';
                $content2iframe = '<iframe id="content-history-iframe-2" class="history-iframe"></iframe>';

                // fragment holen und ausgeben
                $fragment = new rex_fragment();
                $fragment->setVar('title', $addon->i18n('structure_history_overview_versions'));
                $fragment->setVar('content1select', $content1select, false);
                $fragment->setVar('content1iframe', $content1iframe, false);
                $fragment->setVar('content2select', $content2select, false);
                $fragment->setVar('content2iframe', $content2iframe, false);

                echo $fragment->parse('structure/history/layer.php');
                exit;
        }

        rex_extension::register(
            'STRUCTURE_CONTENT_HEADER',
            static function (rex_extension_point $ep) {
                if ('content/edit' == $ep->getParam('page')) {
                    $articleLink = rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId());
                    if (str_starts_with($articleLink, 'http')) {
                        $user = rex::requireUser();
                        $userLogin = $user->getLogin();
                        $historyValidTime = new DateTime();
                        $historyValidTime = $historyValidTime->modify('+10 Minutes')->format(
                            'YmdHis'
                        ); // 10 minutes valid key
                        $userHistorySession = rex_history_login::createSessionKey(
                            $userLogin,
                            $user->getValue('session_id'),
                            $historyValidTime
                        );
                        $articleLink = rex_getUrl(
                            rex_article::getCurrentId(),
                            rex_clang::getCurrentId(),
                            [
                                rex_history_login::class => $userLogin,
                                'rex_history_session' => $userHistorySession,
                                'rex_history_validtime' => $historyValidTime,
                            ],
                        );
                    }

                    echo '<script nonce="'.rex_response::getNonce().'">
                        var history_article_id = '.rex_article::getCurrentId().';
                        var history_clang_id = '.rex_clang::getCurrentId().';
                        var history_ctype_id = '.rex_request('ctype', 'int', 0).';
                        var history_article_link = "'.$articleLink.'";
                        </script>';
                }
            },
        );
    }

    if (rex_addon::get('cronjob')->isAvailable()) {
        rex_cronjob_manager::registerType(rex_cronjob_structure_history::class);
    }
}

// Version extension
if (true === $addon->getConfig('version', false)) {
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

    rex_extension::register('STRUCTURE_CONTENT_BEFORE_SLICES', static function (rex_extension_point $ep) use ($addon) {
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
                    if (true === $addon->getConfig('history', false)) {
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
