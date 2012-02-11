<?php

/**
 * Textile Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

rex_perm::register('textile[]');
rex_perm::register('textile[help]', null, rex_perm::OPTIONS);

if (rex::isBackend())
{
  rex_extension::register('PAGE_HEADER', function($params) {
    $params['subject'] .= "\n  ".
      '<link rel="stylesheet" type="text/css" href="'. rex_path::addonAssets('textile', 'textile.css') .'" />';

    return $params['subject'];
  });
}
