<?php

use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Util\Str;

/**
 * Gibt eine Url zu einem Artikel zurück.
 *
 * @param int|string|null $id
 * @param int|string|null $clang SprachId des Artikels
 * @param array $params Array von Parametern
 *
 * @return string
 */
function rex_getUrl($id = null, $clang = null, array $params = [])
{
    $id = (int) $id;
    $clang = (int) $clang;

    // ----- get id
    if (0 == $id) {
        $id = rex_article::getCurrentId();
    }

    // ----- get clang
    // Wenn eine rexExtension vorhanden ist, immer die clang mitgeben!
    // Die rexExtension muss selbst entscheiden was sie damit macht
    if (!rex_clang::exists($clang) && (rex_clang::count() > 1 || rex_extension::isRegistered('URL_REWRITE'))) {
        $clang = rex_clang::getCurrentId();
    }

    // ----- EXTENSION POINT
    $url = rex_extension::registerPoint(new rex_extension_point('URL_REWRITE', '', ['id' => $id, 'clang' => $clang, 'params' => $params]));

    if ('' == $url) {
        if (rex_clang::count() > 1) {
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
 *
 * @param int|string|null $articleId
 * @param int|string|null $clang SprachId des Artikels
 *
 * @throws InvalidArgumentException
 * @return never
 */
function rex_redirect($articleId, $clang = null, array $params = [])
{
    if (null !== $articleId && '' !== $articleId && !is_int($articleId) && $articleId !== (string) (int) $articleId) {
        throw new InvalidArgumentException(sprintf('"%s" is not a valid article_id!', $articleId));
    }

    rex_response::sendRedirect(rex_getUrl($articleId, $clang, $params));
}
