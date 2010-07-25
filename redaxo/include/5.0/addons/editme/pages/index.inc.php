<?php

/**
 * Version editme
 *
 * @author jan.kristinus@redaxo.de Jan Kristinus
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

require $REX['SRC_PATH'] .'/core/layout/top.php';

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');

rex_title($I18N->msg("editme"),array());

$tables = rex_em_getTables();

switch($subpage)
{
  case 'field':
  	require $REX['SRC_PATH'] . '/core/addons/'.$page.'/pages/'.$subpage.'.inc.php';
    break;

  case 'import':
  	require $REX['SRC_PATH'] . '/core/addons/'.$page.'/pages/'.$subpage.'.inc.php';
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
  		require $REX['SRC_PATH'] . '/core/addons/'.$page.'/pages/'.$subpage.'.inc.php';
  	}elseif($table == "")
  	{
  		echo "-";
  	}else
  	{
  		require $REX['SRC_PATH'] . '/core/addons/'.$page.'/pages/edit.inc.php'; 			
  	}
  }
}

require $REX['SRC_PATH'] .'/core/layout/bottom.php';