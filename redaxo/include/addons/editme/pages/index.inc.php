<?php

/**
 * Version editme
 *
 * @author jan.kristinus@redaxo.de Jan Kristinus
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

require $REX['INCLUDE_PATH'].'/layout/top.php';

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');

rex_title($I18N->msg("editme"),array());

$tables = rex_em_getTables();

switch($subpage)
{
  case 'field':
  	require $REX['INCLUDE_PATH'] . '/addons/'.$page.'/pages/'.$subpage.'.inc.php';
    break;

  case 'import':
  	require $REX['INCLUDE_PATH'] . '/addons/'.$page.'/pages/'.$subpage.'.inc.php';
    break;

  default:
  {
  	$table = "";
  	foreach($tables as $t)
  	{
  		if($t["name"] == $subpage)
  		{
  		  $table = $subpage;
  		}
		}
  	
  	if($table == "" && $REX['USER'] && $REX['USER']->isAdmin())
  	{
  		$subpage = "tables";
  		require $REX['INCLUDE_PATH'] . '/addons/'.$page.'/pages/'.$subpage.'.inc.php';
  	}elseif($table == "")
  	{
  		echo "-";
  	}else
  	{
  		require $REX['INCLUDE_PATH'] . '/addons/'.$page.'/pages/edit.inc.php'; 			
  	}
  }
}

require $REX['INCLUDE_PATH'].'/layout/bottom.php';