<?php

/**
 * History.
 *
 * @author jan@kristinus.de
 *
 * @package redaxo5
 */

$mypage = 'history';
$plugin = rex_plugin::get('structure', 'history');

$history_date = rex_request('rex_history_date', 'string');

rex_perm::register('history[article_rollback]', null, rex_perm::OPTIONS);

if ('' != $history_date) {
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
        $history_date = rex_request('rex_history_date', 'string');
        $article = $ep->getParam('article');

        if ($article instanceof rex_article_content && $article->getArticleId() == rex_article::getCurrentId()) {
            $articleLimit = '';
            if (0 != $article->getArticleId()) {
                $articleLimit = ' AND ' . rex::getTablePrefix() . 'article_slice.article_id=' . $article->getArticleId();
            }

            $sliceLimit = '';

            rex_article_slice_history::checkTables();

            $escapeSql = rex_sql::factory();

            $sliceDate = ' AND ' . rex::getTablePrefix() . 'article_slice.history_date = ' . $escapeSql->escape($history_date);

            return 'SELECT ' . rex::getTablePrefix() . 'module.id, ' . rex::getTablePrefix() . 'module.name, ' . rex::getTablePrefix() . 'module.output, ' . rex::getTablePrefix() . 'module.input, ' . rex::getTablePrefix() . 'article_slice.*, ' . rex::getTablePrefix() . 'article.parent_id
                FROM
                    ' . rex_article_slice_history::getTable() . ' as ' . rex::getTablePrefix() . 'article_slice
                LEFT JOIN ' . rex::getTablePrefix() . 'module ON ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id
                LEFT JOIN ' . rex::getTablePrefix() . 'article ON ' . rex::getTablePrefix() . 'article_slice.article_id=' . rex::getTablePrefix() . 'article.id
                WHERE
                    ' . rex::getTablePrefix() . "article_slice.clang_id='" . $article->getClangId() . "' AND
                    " . rex::getTablePrefix() . "article.clang_id='" . $article->getClangId() . "' AND
                    " . rex::getTablePrefix() . 'article_slice.revision=0
                    ' . $articleLimit . '
                    ' . $sliceLimit . '
                    ' . $sliceDate . '
                    ORDER BY ' . rex::getTablePrefix() . 'article_slice.priority';
        }
    });
}

if (rex::isBackend() && rex::getUser() && rex::getUser()->hasPerm('history[article_rollback]')) {
    rex_extension::register(
        ['ART_SLICES_COPY', 'SLICE_ADD', 'SLICE_UPDATE', 'SLICE_MOVE', 'SLICE_DELETE'],
        static function (rex_extension_point $ep) {
            switch ($ep->getName()) {
                case 'ART_SLICES_COPY':
                    $type = 'slices_copy';
                    break;
                case 'SLICE_MOVE':
                    $type = 'slice_' . $ep->getParam('direction');
                    break;
                default:
                    $type = strtolower($ep->getName());
            }

            $article_id = $ep->getParam('article_id');
            $clang_id = $ep->getParam('clang_id');
            $slice_revision = $ep->getParam('slice_revision');

            if (0 == $slice_revision) {
                rex_article_slice_history::makeSnapshot($article_id, $clang_id, $type);
            }
        }
    );

    rex_view::addCssFile($plugin->getAssetsUrl('noUiSlider/nouislider.css'));
    rex_view::addJsFile($plugin->getAssetsUrl('noUiSlider/nouislider.js'), [rex_view::JS_IMMUTABLE => true]);
    rex_view::addCssFile($plugin->getAssetsUrl('history.css'));
    rex_view::addJsFile($plugin->getAssetsUrl('history.js'), [rex_view::JS_IMMUTABLE => true]);

    switch (rex_request('rex_history_function', 'string')) {
        case 'snap':
            $article_id = rex_request('history_article_id', 'int');
            $clang_id = rex_request('history_clang_id', 'int');
            $history_date = rex_request('history_date', 'string');
            rex_article_slice_history::restoreSnapshot($history_date, $article_id, $clang_id);

            // no break
        case 'layer':

            $article_id = rex_request('history_article_id', 'int');
            $clang_id = rex_request('history_clang_id', 'int');
            $versions = rex_article_slice_history::getSnapshots($article_id, $clang_id);

            $select1 = [];
            $select1[] = '<option value="0" selected="selected" data-revision="0">' . $plugin->i18n('current_version') . '</option>';
            if (rex_plugin::get('structure', 'version')->isAvailable()) {
                $select1[] = '<option value="1" data-revision="1">' . rex_i18n::msg('version_workingversion') . '</option>';
            }

            $select2 = [];
            $select2[] = '<option value="" selected="selected">' . $plugin->i18n('current_version') . '</option>';
            foreach ($versions as $version) {
                $history_info = $version['history_date'];
                if ('' != $version['history_user']) {
                    $history_info = $version['history_date'] . ' [' . $version['history_user'] . ']';
                }
                $select2[] = '<option value="' . strtotime($version['history_date']) . '" data-history-date="' . $version['history_date'] . '">' . $history_info . '</option>';
            }

            $content1select = '<select id="content-history-select-date-1" class="content-history-select" data-iframe="content-history-iframe-1" style="">' . implode('', $select1) . '</select>';
            $content1iframe = '<iframe id="content-history-iframe-1" class="history-iframe"></iframe>';
            $content2select = '<select id="content-history-select-date-2" class="content-history-select" data-iframe="content-history-iframe-2">' . implode('', $select2) . '</select>';
            $content2iframe = '<iframe id="content-history-iframe-2" class="history-iframe"></iframe>';

            // fragment holen und ausgeben
            $fragment = new rex_fragment();
            $fragment->setVar('title', $plugin->i18n('overview_versions'));
            $fragment->setVar('content1select', $content1select, false);
            $fragment->setVar('content1iframe', $content1iframe, false);
            $fragment->setVar('content2select', $content2select, false);
            $fragment->setVar('content2iframe', $content2iframe, false);

            echo $fragment->parse('history/layer.php');
            exit;
    }

    rex_extension::register('STRUCTURE_CONTENT_HEADER', static function (rex_extension_point $ep) {
        if ('content/edit' == $ep->getParam('page')) {
            $article_link = rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId(), [], '&');
            if ('http' == substr($article_link, 0, 4)) {
                $user = rex::getUser();
                $userLogin = $user->getLogin();
                $historyValidTime = new DateTime();
                $historyValidTime = $historyValidTime->modify('+10 Minutes')->format('YmdHis'); // 10 minutes valid key
                $userHistorySession = rex_history_login::createSessionKey($userLogin, $user->getValue('session_id'), $historyValidTime);
                $article_link = rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId(), ['rex_history_login' => $userLogin, 'rex_history_session' => $userHistorySession, 'rex_history_validtime' => $historyValidTime], '&');
            }

            echo '<script>
                    var history_article_id = ' . rex_article::getCurrentId() . ';
                    var history_clang_id = ' . rex_clang::getCurrentId() . ';
                    var history_ctype_id = ' . rex_request('ctype', 'int', 0) . ';
                    var history_article_link = "' . $article_link . '";
                    </script>';
        }
    }
    );
}

if (rex_addon::get('cronjob')->isAvailable()) {
    rex_cronjob_manager::registerType(rex_cronjob_structure_history::class);
}
