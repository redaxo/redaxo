<?php

/**
 * URL Funktionen
 * @package redaxo\structure
 */

/**
 * @param string $name
 * @return string
 * @package redaxo\structure
 */
function rex_parse_article_name($name)
{
    return
        // + durch - ersetzen
        str_replace('+', '-',
            // ggf uebrige zeichen url-codieren
            urlencode(
                // mehrfach hintereinander auftretende spaces auf eines reduzieren
                preg_replace('/ {2,}/', ' ',
                    // alle sonderzeichen raus
                    rex_string::normalize($name, '', ' _')
                )
            )
        );
}

/**
 * Baut einen Parameter String anhand des array $params
 *
 * @package redaxo\structure
 */
function rex_param_string($params, $divider = '&amp;')
{
    $param_string = '';

    if (is_array($params)) {
        foreach ($params as $key => $value) {
            $param_string .= $divider . urlencode($key) . '=' . urlencode($value);
        }
    } elseif ($params != '') {
        $param_string = $params;
    }

    return $param_string;
}

/**
 * Gibt eine Url zu einem Artikel zurück
 *
 * @param string       $_id
 * @param int|string   $_clang  SprachId des Artikels
 * @param array|string $_params Array von Parametern
 * @param bool         $escape  Flag whether the argument separator "&" should be escaped (&amp;)
 * @return string
 * @package redaxo\structure
 */
function rex_getUrl($_id = '', $_clang = '', $_params = '', $escape = true)
{
    $id = (int) $_id;
    $clang = (int) $_clang;

    // ----- get id
    if ($id == 0) {
        $id = rex::getProperty('article_id');
    }

    // ----- get clang
    // Wenn eine rexExtension vorhanden ist, immer die clang mitgeben!
    // Die rexExtension muss selbst entscheiden was sie damit macht
    if ($_clang === '' && (rex_clang::count() > 1 || rex_extension::isRegistered( 'URL_REWRITE'))) {
        $clang = rex_clang::getCurrentId();
    }

    // ----- get params
    $param_string = rex_param_string($_params, $escape ? '&amp;' : '&');

    $name = 'NoName';
    if ($id != 0) {
        $ooa = rex_article::get($id, $clang);
        if ($ooa) {
            $name = rex_parse_article_name($ooa->getName());
        }
    }

    // ----- EXTENSION POINT
    $url = rex_extension::registerPoint(new rex_extension_point('URL_REWRITE', '', ['id' => $id, 'name' => $name, 'clang' => $clang, 'params' => $param_string, 'escape' => $escape]));

    if ($url == '') {
        $_clang = '';
        if (rex_clang::count() > 1) {
            $_clang .= ($escape ? '&amp;' : '&') . 'clang=' . $clang;
        }

        $url = rex_url::frontendController() . '?article_id=' . $id . $_clang . $param_string;
    }

    return $url;
}

/**
 * Leitet auf einen anderen Artikel weiter
 *
 * @package redaxo\structure
 */
function rex_redirect($article_id, $clang = '', $params = [])
{
    // Alle OBs schließen
    while (@ob_end_clean());

    header('Location: ' . rex_getUrl($article_id, $clang, $params, false));
    exit();
}
