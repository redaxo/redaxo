<?php

/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo5.2
 */

abstract class rex_url_rewriter
{
  // Konstruktor
  public function __construct()
  {
    // nichts tun
  }

  // Parameter aus der URL für das Script verarbeiten
  public function prepare()
  {
    // nichts tun
  }

  // Url neu schreiben
  public function rewrite(array $params)
  {
    $id = $params['id'];
    $name = $params['name'];
    $clang = $params['clang'];
    $params = $params['params'];
    return rex_no_rewrite($id, $name, $clang, $params);
  }
}
