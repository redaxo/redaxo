<?php

/**
 * History.
 *
 * @author jan@kristinus.de
 *
 * @package redaxo5
 */

$mypage = 'history';
$history_date = rex_request('rex_history_date', 'string');

rex_perm::register('history[article_rollback]', null, rex_perm::OPTIONS);

if ($history_date != '') {
    $userSession = rex_request('rex_history_session', 'string');
    $userLogin =  rex_request('rex_history_login', 'string');

    if ($userSession != '' && $userLogin != '' && !rex::isBackend()) {
        $login = new rex_history_login();

        if ($login->checkSessionLogin($userSession, $userLogin)) {
            $user = $login->getUser();
            rex::setProperty('user', $user);
            rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) use ($login) {
                $login->deleteSession();
            });
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

    rex_extension::register('ART_INIT', function (rex_extension_point $ep) {
        $article = $ep->getParam('article');
        if ($article instanceof rex_article_content) {
            $article->getContentAsQuery();
        }
        $article->setEval(true);
    });

    rex_extension::register('ART_SLICES_QUERY', function (rex_extension_point $ep) {
        $history_date = rex_request('rex_history_date', 'string');
        $history_revision = rex_request('history_revision', 'int', 0);
        $article = $ep->getParam('article');

        if ($article instanceof rex_article_content && $article->getArticleId() == rex_article::getCurrentId()) {
            $articleLimit = '';
            if ($article->getArticleId() != 0) {
                $articleLimit = ' AND ' . rex::getTablePrefix() . 'article_slice.article_id=' . $article->getArticleId();
            }

            $sliceLimit = '';

            $escapeSql = rex_sql::factory();

            $sliceDate = ' AND ' . rex::getTablePrefix() . 'article_slice.history_date = ' . $escapeSql->escape($history_date);

            return 'SELECT ' . rex::getTablePrefix() . 'module.id, ' . rex::getTablePrefix() . 'module.name, ' . rex::getTablePrefix() . 'module.output, ' . rex::getTablePrefix() . 'module.input, ' . rex::getTablePrefix() . 'article_slice.*, ' . rex::getTablePrefix() . 'article.parent_id
                FROM
                    ' . rex_article_slice_history::getTable() . ' as ' . rex::getTablePrefix() . 'article_slice
                LEFT JOIN ' . rex::getTablePrefix() . 'module ON ' . rex::getTablePrefix() . 'article_slice.module_id=' . rex::getTablePrefix() . 'module.id
                LEFT JOIN ' . rex::getTablePrefix() . 'article ON ' . rex::getTablePrefix() . 'article_slice.article_id=' . rex::getTablePrefix() . 'article.id
                WHERE
                    ' . rex::getTablePrefix() . "article_slice.clang_id='" . $article->getClang() . "' AND
                    " . rex::getTablePrefix() . "article.clang_id='" . $article->getClang() . "' AND
                    " . rex::getTablePrefix() . "article_slice.revision='" . $history_revision . "'
                    " . $articleLimit . '
                    ' . $sliceLimit . '
                    ' . $sliceDate . '
                    ORDER BY ' . rex::getTablePrefix() . 'article_slice.priority';
        }
    });
}

if (rex::isBackend() && rex::getUser() && rex::getUser()->hasPerm('history[article_rollback]')) {
    rex_extension::register(
        ['ART_SLICES_COPY', 'SLICE_ADD', 'SLICE_UPDATE', 'SLICE_MOVE', 'SLICE_DELETE'],
        function (rex_extension_point $ep) {
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

            rex_article_slice_history::makeSnapshot($article_id, $clang_id, $type, $slice_revision);
        }
    );

    rex_view::addCssFile($this->getAssetsUrl('history.css'));
    rex_view::addJsFile($this->getAssetsUrl('history.js'));

    $info = '';
    switch (rex_request('rex_history_function', 'string')) {
        case 'snap':
            $article_id = rex_request('history_article_id', 'int');
            $clang_id = rex_request('history_clang_id', 'int');
            $revision = rex_request('history_revision', 'int', 0);
            $history_date = rex_request('history_date', 'string');

            rex_article_slice_history::restoreSnapshot($history_date, $article_id, $clang_id, $revision);

            $info = $version['history_snapshot_history_reactivate_snapshot'];

        case 'layer':

            // article_id und clang_id und revision noch nötig

            $article_id = rex_request('history_article_id', 'int');
            $clang_id = rex_request('history_clang_id', 'int');
            $revision = rex_request('history_revision', 'int', 0);

            $versions = rex_article_slice_history::getSnapshots($article_id, $clang_id, $revision);

            $select = '<option value="" selected="selected">' . $this->i18n('current_version') . '</option>';
            foreach ($versions as $version) {
                $history_info = $version['history_date'];
                if ($version['history_user'] != '') {
                    $history_info = $version['history_date'] . ' [' . $this->i18n('savedby') . ' ' . $version['history_user'] . ']';
                }
                $select .= '<option value="' . $version['history_date'] . '">' . $history_info . '</option>';
            }
            $content1select = '<select id="content-history-select-date-1" class="content-history-select" data-iframe="content-history-iframe-1" style="">' . $select . '</select>';
            $content1iframe = '<iframe id="content-history-iframe-1" class="history-iframe"></iframe>';
            $content2select = '<select id="content-history-select-date-2" class="content-history-select" data-iframe="content-history-iframe-2">' . $select . '</select>';
            $content2iframe = '<iframe id="content-history-iframe-2" class="history-iframe"></iframe>';
            $button_restore = '<a class="btn btn-apply" href="javascript:rex_history_snapVersion(\'content-history-select-date-2\');">' . $this->i18n('snapshot_reactivate') . '</a>';

            // fragment holen und ausgeben
            $fragment = new rex_fragment();
            $fragment->setVar('title', $this->i18n('overview_versions'));
            $fragment->setVar('info', $info, false);
            $fragment->setVar('content1select', $content1select, false);
            $fragment->setVar('content1iframe', $content1iframe, false);
            $fragment->setVar('content2select', $content2select, false);
            $fragment->setVar('content2iframe', $content2iframe, false);

            $fragment->setVar('button_restore', $button_restore, false);

            echo $fragment->parse('history/layer.php');
            exit;
    }

    rex_extension::register('STRUCTURE_CONTENT_HEADER', function (rex_extension_point $ep) {
        if ($ep->getParam('page') == 'content/edit') {

            $user = rex::getUser();
            $userSession = $user->getValue('session_id');
            $userLogin = $user->getLogin();

            echo '<script>
                    var history_article_id = ' . rex_article::getCurrentId() . ';
                    var history_clang_id = ' . rex_clang::getCurrentId() . ';
                    var history_ctype_id = ' . rex_request('ctype', 'int', 0) . ';
                    var history_revision = ' . rex_request('rex_set_version', 'int', 0) . ';
                    var history_article_link = "' . rex_getUrl(rex_article::getCurrentId(), rex_clang::getCurrentId(), ['history_revision' => rex_request('rex_set_version', 'int', 0), 'rex_history_login' => $userLogin, 'rex_history_session' => $userSession], '&') . '";
                    </script>';
        }
    }
    );
}
