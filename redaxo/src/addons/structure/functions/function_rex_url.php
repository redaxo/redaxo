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
    static $firstCall = true;
    static $search, $replace;

    if ($firstCall) {
        // Sprachspezifische Sonderzeichen Filtern
        $search = explode('|', rex_i18n::msg('special_chars'));
        $replace = explode('|', rex_i18n::msg('special_chars_rewrite'));

        $firstCall = false;
    }

    return
        // + durch - ersetzen
        str_replace('+', '-',
                // ggf uebrige zeichen url-codieren
                urlencode(
                    // mehrfach hintereinander auftretende spaces auf eines reduzieren
                    preg_replace('/ {2,}/', ' ',
                        // alle sonderzeichen raus
                        preg_replace('/[^a-zA-Z_\-0-9 ]/', '',
                            // sprachspezifische zeichen umschreiben
                            str_replace($search, $replace, $name)
                        )
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
 * @param int|string   $_clang   SprachId des Artikels
 * @param array|string $_params  Array von Parametern
 * @param string       $_divider Trennzeichen für Parameter (z.B. &amp; für HTML, & für Javascript)
 * @return string
 * @package redaxo\structure
 */
function rex_getUrl($_id = '', $_clang = '', $_params = '', $_divider = '&amp;')
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
    $param_string = rex_param_string($_params, $_divider);

    $name = 'NoName';
    if ($id != 0) {
        $ooa = rex_article :: getArticleById($id, $clang);
        if ($ooa) {
            $name = rex_parse_article_name($ooa->getName());
        }
    }

    // ----- EXTENSION POINT
    $url = rex_extension::registerPoint(new rex_extension_point('URL_REWRITE', '', ['id' => $id, 'name' => $name, 'clang' => $clang, 'params' => $param_string, 'divider' => $_divider]));

    if ($url == '') {
        $_clang = '';
        if (rex_clang::count() > 1) {
            $_clang .= $_divider . 'clang=' . $clang;
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

    $divider = '&';

    header('Location: ' . rex_getUrl($article_id, $clang, $params, $divider));
    exit();
}
