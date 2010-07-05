<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4.2
 * @version svn:$Id$
 */

class rexUrlRewriter
{
  // Konstruktor
  function rexUrlRewriter()
  {
    // nichts tun
  }

  // Parameter aus der URL für das Script verarbeiten
  function prepare()
  {
    // nichts tun
  }

  // Url neu schreiben
  function rewrite($params)
  {
    $id = $params['id'];
    $name = $params['name'];
    $clang = $params['clang'];
    $params = $params['params'];
    return rex_no_rewrite($id, $name, $clang, $params);
  }
}