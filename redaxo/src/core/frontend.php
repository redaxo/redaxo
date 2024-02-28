<?php

if (rex::isSetup()) {
    rex_response::sendRedirect(rex_url::backendController());
}

if (rex::isDebugMode()) {
    header('X-Robots-Tag: noindex, nofollow, noarchive');
}

if (0 != rex::getConfig('phpmailer_errormail')) {
    rex_extension::register('RESPONSE_SHUTDOWN', static function () {
        rex_mailer::errorMail();
    });
}

// ----- INCLUDE ADDONS
include_once rex_path::core('packages.php');

// ----- caching end für output filter
$content = ob_get_clean();

// trigger api functions. the api function is responsible for checking permissions.
rex_api_function::handleCall();

if (rex_extension::isRegistered('FE_OUTPUT')) {
    // ----- EXTENSION POINT
    rex_extension::registerPoint(new rex_extension_point('FE_OUTPUT', $content));

    return;
}

if (rex::getConfig('article_history', false)) {
    $historyDate = rex_request('rex_history_date', 'string');

    if ('' != $historyDate) {
        $historySession = rex_request('rex_history_session', 'string');
        $historyLogin = rex_request('rex_history_login', 'string');
        $historyValidtime = rex_request('rex_history_validtime', 'string');

        $user = null;
        if ('' != $historySession && '' != $historyLogin && '' != $historyValidtime) {
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
                    $articleLimit = ' AND ' . rex::getTablePrefix() . 'article_slice.article_id=' . $article->getArticleId();
                }

                $sliceLimit = '';

                rex_article_slice_history::checkTables();

                $escapeSql = rex_sql::factory();

                $sliceDate = ' AND ' . rex::getTablePrefix() . 'article_slice.history_date = ' . $escapeSql->escape($historyDate);

                return 'SELECT ' . rex::getTablePrefix() . 'module.id, ' . rex::getTablePrefix() . 'module.key,' . rex::getTablePrefix() . 'module.name, ' . rex::getTablePrefix() . 'module.output, ' . rex::getTablePrefix() . 'module.input, ' . rex::getTablePrefix() . 'article_slice.*, ' . rex::getTablePrefix() . 'article.parent_id
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

            return null;
        });
    }
}

$clangId = rex_get('clang', 'int');
if ($clangId && !rex_clang::exists($clangId)) {
    rex_redirect(rex_article::getNotfoundArticleId(), rex_clang::getStartId());
}

$article = new rex_article_content();
$article->setClang(rex_clang::getCurrentId());

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
    $article->setClang(rex_clang::getCurrentId());
    $article->setArticleId(rex_article::getNotfoundArticleId());

    $content .= $article->getArticleTemplate();
}

$artId = $article->getArticleId();
if ($artId == rex_article::getNotfoundArticleId() && $artId != rex_article::getSiteStartArticleId()) {
    rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
}

// ----- inhalt ausgeben
rex_response::sendPage($content, $article->getValue('updatedate'));
