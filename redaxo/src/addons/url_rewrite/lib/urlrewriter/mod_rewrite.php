<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo5.2
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
    public function rewrite(array $params)
    {
        // Url wurde von einer anderen Extension bereits gesetzt
        if ($params['subject'] != '') {
            return $params['subject'];
        }

        $params['params'] = $params['params'] == '' ? '' : '?' . $params['params'];
        return $params['id'] . '-' . $params['clang'] . '-' . $params['name'] . '.htm' . $params['params'];
    }
}
