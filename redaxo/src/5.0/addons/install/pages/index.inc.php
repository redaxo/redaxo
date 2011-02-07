<?php

/**
 * Install
 *
 * @author 
 * 
 * @package redaxo5
 * @version svn:$Id$
 */

require $REX['INCLUDE_PATH'] .'/core/layout/top.php';

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');

rex_title($REX['I18N']->msg("install_name"),array());


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

    require $REX['INCLUDE_PATH'] . '/addons/'.$page.'/pages/'.$subpage.'.inc.php';


require $REX['INCLUDE_PATH'] .'/core/layout/bottom.php';