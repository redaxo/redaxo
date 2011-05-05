<?php

/**
 * Install
 *
 * @author
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');

rex_title(rex_i18n::msg("install_name"),array());


// addons
// plugins
// modules
// templates
// sprachpaket
// update core


switch($subpage)
{
  case 'addons':
  default:
  	$subpage = "addons";
    break;
}

    require rex_path::addon($page, 'pages/'.$subpage.'.inc.php');