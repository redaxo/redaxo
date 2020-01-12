<?php

/**
 * URL Funktionen.
 *
 * @package redaxo\structure
 */

/**
 * Gibt eine Url zu einem Artikel zurück.
 *
 * @param int|string|null $id
 * @param int|string|null $clang     SprachId des Artikels
 * @param array           $params    Array von Parametern
 * @param string          $separator
 *
 * @return string
 *
 * @package redaxo\structure
 */
function rex_getUrl($id = null, $clang = null, array $params = [], $separator = '&amp;')
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
    $url = rex_extension::registerPoint(new rex_extension_point('URL_REWRITE', '', ['id' => $id, 'clang' => $clang, 'params' => $params, 'separator' => $separator]));

    if ('' == $url) {
        if (rex_clang::count() > 1) {
            $clang = $separator . 'clang=' . $clang;
        } else {
            $clang = '';
        }

        $params = rex_string::buildQuery($params, $separator);
        $params = $params ? $separator . $params : '';

        $url = rex_url::frontendController() . '?article_id=' . $id . $clang . $params;
    }

    return $url;
}

/**
 * Leitet auf einen anderen Artikel weiter.
 *
 * @param null|int|string $article_id
 * @param null|int|string $clang      SprachId des Artikels
 *
 * @throws InvalidArgumentException
 *
 * @package redaxo\structure
 */
function rex_redirect($article_id, $clang = null, array $params = [])
{
    if (null !== $article_id && '' !== $article_id && !is_int($article_id) && $article_id !== (string) (int) $article_id) {
        throw new InvalidArgumentException(sprintf('"%s" is not a valid article_id!', $article_id));
    }

    rex_response::sendRedirect(rex_getUrl($article_id, $clang, $params, '&'));
}
