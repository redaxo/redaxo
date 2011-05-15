<?php

/**
 * Fügt die benötigen Assets ein
 *
 * @param $params Extension-Point Parameter
 */
function rex_a655_add_assets($params)
{
  $addon = 'be_dashboard';

  if(rex_core::getProperty('page') != $addon) return '';

  $params['subject'] .= "\n  ".
    '<link rel="stylesheet" type="text/css" href="'. rex_path::addonAssets($addon, 'be_dashboard.css', rex_path::RELATIVE) .'" />';
  $params['subject'] .= "\n  ".
    '<script type="text/javascript" src="'. rex_path::addonAssets($addon, 'be_dashboard.js', rex_path::RELATIVE) .'"></script>';

  return $params['subject'];
}
