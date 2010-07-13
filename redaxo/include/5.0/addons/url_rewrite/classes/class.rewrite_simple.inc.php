<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4.2
 * @version svn:$Id$
 */

/**
 * URL Simple Rewrite Anleitung:
 * 
 *   .htaccess file in das root verzeichnis:
 *     RewriteEngine Off
 */
class myUrlRewriter extends rexUrlRewriter
{
  // Konstruktor
  function myUrlRewriter()
  {
    // Parent Konstruktor aufrufen
    $this->rexUrlRewriter();
  }

  // Parameter aus der URL für das Script verarbeiten
  function prepare()
  {
    global $article_id, $clang, $REX;

    if (ereg('^/([0-9]*)-([0-9]*)', $_SERVER['QUERY_STRING'], $_match))
    {
      $article_id = $_match[1];
      $clang = $_match[2];
    }
    elseif ((empty( $_GET['article_id'])) && ( empty( $_POST['article_id'])))
    {
      $article_id = $REX['START_ARTICLE_ID'];
    }
  }

  // Url neu schreiben
  function rewrite($params)
  {
  	// Url wurde von einer anderen Extension bereits gesetzt
  	if($params['subject'] != '')
  	{
  		return $params['subject'];
  	}
  	
  	return '?/'.$params['id'].'-'.$params['clang'].'-'.$params['name'].'.htm'.$params['params'];
  }
}