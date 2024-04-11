<?php

use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\ArticleContent;
use Redaxo\Core\Content\ArticleContentBase;
use Redaxo\Core\Content\ArticleRevision;
use Redaxo\Core\Content\ArticleSliceHistory;
use Redaxo\Core\Content\HistoryLogin;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Mailer\Mailer;

if (Core::isSetup()) {
    rex_response::sendRedirect(Url::backendController());
}

if (Core::isDebugMode()) {
    header('X-Robots-Tag: noindex, nofollow, noarchive');
}

if (0 != Core::getConfig('phpmailer_errormail')) {
    rex_extension::register('RESPONSE_SHUTDOWN', static function () {
        Mailer::errorMail();
    });
}

// ----- INCLUDE ADDONS
include_once Path::core('packages.php');

// ----- caching end für output filter
$content = ob_get_clean();

// trigger api functions. the api function is responsible for checking permissions.
rex_api_function::handleCall();

if (rex_extension::isRegistered('FE_OUTPUT')) {
    // ----- EXTENSION POINT
    rex_extension::registerPoint(new rex_extension_point('FE_OUTPUT', $content));

    return;
}

if (Core::getConfig('article_history', false)) {
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
                $login = new HistoryLogin();

                if ($login->checkTempSession($historyLogin, $historySession, $historyValidtime)) {
                    $user = $login->getUser();
                    Core::setProperty('user', $user);
                    rex_extension::register('OUTPUT_FILTER', static function (rex_extension_point $ep) use ($login) {
                        $login->deleteSession();
                    });
                }
            }
        } else {
            $user = rex_backend_login::createUser();
        }

        if (!$user) {
            throw new rex_http_exception(new rex_exception('no permission'), rex_response::HTTP_UNAUTHORIZED);
        }

        if (!$user->hasPerm('history[article_rollback]')) {
            throw new rex_http_exception(new rex_exception('no permission for the slice version'), rex_response::HTTP_FORBIDDEN);
        }

        rex_extension::register('ART_INIT', static function (rex_extension_point $ep) {
            $article = $ep->getParam('article');
            if ($article instanceof ArticleContent) {
                $article->getContentAsQuery();
            }
            $article->setEval(true);
        });

        rex_extension::register('ART_SLICES_QUERY', static function (rex_extension_point $ep) {
            $historyDate = rex_request('rex_history_date', 'string');
            $article = $ep->getParam('article');

            if ($article instanceof ArticleContent && $article->getArticleId() == Article::getCurrentId()) {
                $articleLimit = '';
                if (0 != $article->getArticleId()) {
                    $articleLimit = ' AND ' . Core::getTablePrefix() . 'article_slice.article_id=' . $article->getArticleId();
                }

                ArticleSliceHistory::checkTables();

                $escapeSql = Sql::factory();

                $sliceDate = ' AND ' . Core::getTablePrefix() . 'article_slice.history_date = ' . $escapeSql->escape($historyDate);

                return 'SELECT ' . Core::getTablePrefix() . 'module.id, ' . Core::getTablePrefix() . 'module.key,' . Core::getTablePrefix() . 'module.name, ' . Core::getTablePrefix() . 'module.output, ' . Core::getTablePrefix() . 'module.input, ' . Core::getTablePrefix() . 'article_slice.*, ' . Core::getTablePrefix() . 'article.parent_id
                    FROM
                        ' . ArticleSliceHistory::getTable() . ' as ' . Core::getTablePrefix() . 'article_slice
                    LEFT JOIN ' . Core::getTablePrefix() . 'module ON ' . Core::getTablePrefix() . 'article_slice.module_id=' . Core::getTablePrefix() . 'module.id
                    LEFT JOIN ' . Core::getTablePrefix() . 'article ON ' . Core::getTablePrefix() . 'article_slice.article_id=' . Core::getTablePrefix() . 'article.id
                    WHERE
                        ' . Core::getTablePrefix() . "article_slice.clang_id='" . $article->getClangId() . "' AND
                        " . Core::getTablePrefix() . "article.clang_id='" . $article->getClangId() . "' AND
                        " . Core::getTablePrefix() . 'article_slice.revision=0
                        ' . $articleLimit . '
                        ' . $sliceDate . '
                        ORDER BY ' . Core::getTablePrefix() . 'article_slice.priority';
            }

            return null;
        });
    }
}

if (Core::getConfig('article_work_version', false)) {
    rex_extension::register('ART_INIT', static function (rex_extension_point $ep) {
        $version = rex_request('rex_version', 'int');
        if (ArticleRevision::WORK != $version) {
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

        /** @var ArticleContentBase $article */
        $article = $ep->getParam('article');
        $article->setSliceRevision($version);
        if ($article instanceof ArticleContent) {
            $article->getContentAsQuery();
        }
        $article->setEval(true);
    });
}

$clangId = rex_get('clang', 'int');
if ($clangId && !Language::exists($clangId)) {
    rex_redirect(Article::getNotfoundArticleId(), Language::getStartId());
}

$article = new ArticleContent();
$article->setClang(Language::getCurrentId());

if (!$article->setArticleId(Article::getCurrentId())) {
    if (!Core::isDebugMode() && !rex_backend_login::hasSession()) {
        throw new rex_exception('Article with id ' . Article::getCurrentId() . ' does not exist');
    }

    $fragment = new rex_fragment([
        'content' => '<p><b>Article with ID ' . Article::getCurrentId() . ' not found.</b><br />If this is a fresh setup, an article must be created first.<br />Enter <a href="' . Url::backendController() . '">REDAXO</a>.</p>',
    ]);
    $content .= $fragment->parse('core/fe_ooops.php');
    rex_response::sendPage($content);
    exit;
}

try {
    $content .= $article->getArticleTemplate();
} catch (rex_article_not_found_exception) {
    $article = new ArticleContent();
    $article->setClang(Language::getCurrentId());
    $article->setArticleId(Article::getNotfoundArticleId());

    $content .= $article->getArticleTemplate();
}

$artId = $article->getArticleId();
if ($artId == Article::getNotfoundArticleId() && $artId != Article::getSiteStartArticleId()) {
    rex_response::setStatus(rex_response::HTTP_NOT_FOUND);
}

// ----- inhalt ausgeben
rex_response::sendPage($content, $article->getValue('updatedate'));
