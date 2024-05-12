<?php

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Language\Language;
use Redaxo\Core\Util\Str;

/**
 * Gibt eine Url zu einem Artikel zurück.
 *
 * @param array $params Array von Parametern
 */
function rex_getUrl(?int $id = null, ?int $clang = null, array $params = []): string
{
    $clang = (int) $clang;

    // ----- get id
    if (!$id) {
        $id = Article::getCurrentId();
    }

    // ----- get clang
    // Wenn eine rexExtension vorhanden ist, immer die clang mitgeben!
    // Die rexExtension muss selbst entscheiden was sie damit macht
    if (!Language::exists($clang) && (Language::count() > 1 || Extension::isRegistered('URL_REWRITE'))) {
        $clang = Language::getCurrentId();
    }

    // ----- EXTENSION POINT
    $url = Extension::registerPoint(new ExtensionPoint('URL_REWRITE', '', ['id' => $id, 'clang' => $clang, 'params' => $params]));

    if ('' == $url) {
        if (Language::count() > 1) {
            $clang = '&clang=' . $clang;
        } else {
            $clang = '';
        }

        $params = Str::buildQuery($params);
        $params = $params ? '&' . $params : '';

        $url = Url::frontendController() . '?article_id=' . $id . $clang . $params;
    }

    return $url;
}

/**
 * Leitet auf einen anderen Artikel weiter.
 */
function rex_redirect(?int $articleId = null, ?int $clang = null, array $params = []): never
{
    rex_response::sendRedirect(rex_getUrl($articleId, $clang, $params));
}
