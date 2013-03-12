<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo\url-rewrite
 */

/**
 * URL Mod Rewrite Anleitung:
 *
 *   .htaccess file in das root verzeichnis:
 *     RewriteEngine On
 *     # RewriteBase /
 *     RewriteRule ^([0-9]*)-([0-9]*)- index.php?article_id=$1&clang=$2&%{QUERY_STRING}
 */
class rex_url_rewriter_mod_rewrite extends rex_url_rewriter
{
    // Url neu schreiben
    public function rewrite(rex_extension_point $ep)
    {
        // Url wurde von einer anderen Extension bereits gesetzt
        if ($ep->getSubject() != '') {
            return;
        }
        $params = $ep->getParams();
        $params['params'] = $params['params'] == '' ? '' : '?' . $params['params'];
        return $params['id'] . '-' . $params['clang'] . '-' . $params['name'] . '.htm' . $params['params'];
    }
}
