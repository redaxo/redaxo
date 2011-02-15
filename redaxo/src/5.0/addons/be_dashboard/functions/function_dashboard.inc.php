<?php

/**
 * Fügt die benötigen Assets ein
 *
 * @param $params Extension-Point Parameter
 */
function rex_a655_add_assets($params)
{
  global $REX;

  $addon = 'be_dashboard';

  if($REX['PAGE'] != $addon) return '';

  $params['subject'] .= "\n  ".
    '<link rel="stylesheet" type="text/css" href="'. rex_path::addonAssets($addon, 'be_dashboard.css') .'" />';
  $params['subject'] .= "\n  ".
    '<script type="text/javascript" src="'. rex_path::addonAssets($addon, 'be_dashboard.js') .'"></script>';

  return $params['subject'];
}
