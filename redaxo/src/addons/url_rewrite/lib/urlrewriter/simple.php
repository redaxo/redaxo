<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo5.2
 */

/**
 * URL Simple Rewrite Anleitung:
 *
 *   .htaccess file in das root verzeichnis:
 *     RewriteEngine Off
 */
class rex_urlRewriter_simple extends rex_urlRewriter
{

  // Parameter aus der URL für das Script verarbeiten
  public function prepare()
  {
    global $article_id, $clang;

    if (ereg('^/([0-9]*)-([0-9]*)', $_SERVER['QUERY_STRING'], $_match))
    {
      $article_id = $_match[1];
      $clang = $_match[2];
    }
    elseif ((empty( $_GET['article_id'])) && ( empty( $_POST['article_id'])))
    {
      $article_id = rex::getProperty('start_article_id');
    }
  }

  // Url neu schreiben
  public function rewrite(array $params)
  {
    // Url wurde von einer anderen Extension bereits gesetzt
    if ($params['subject'] != '')
    {
      return $params['subject'];
    }

    return '?/'.$params['id'].'-'.$params['clang'].'-'.$params['name'].'.htm'.$params['params'];
  }
}
