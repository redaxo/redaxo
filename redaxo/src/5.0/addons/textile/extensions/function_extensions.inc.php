<?php

/**
 * Fügt die benötigen Stylesheets ein
 *
 * @param $params Extension-Point Parameter
 */
function rex_a79_css_add($params)
{
  $addon = 'textile';

  $params['subject'] .= "\n  ".
    '<link rel="stylesheet" type="text/css" href="'. rex_path::addonAssets($addon, 'textile.css', true) .'" />';

  return $params['subject'];
}
