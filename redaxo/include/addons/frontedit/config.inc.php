<?php

// TODO:
// Achtung .. Wenn Ÿberhauptkein Artikel vorhanden ist kam Fehler (leere Installation)
// 


/**
 * Frontedit
 *
 * @author jan.kristinus@yakamara.de
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = "frontedit";
$REX['ADDON']['rxid'][$mypage] = '';
// $REX['ADDON']['name'][$mypage] = 'Frontedit';
// $REX['ADDON']['perm'][$mypage] = 'frontedit[]';
$REX['ADDON']['version'][$mypage] = '0.2';
$REX['ADDON']['author'][$mypage] = 'Jan Kristinus';
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';

$REX['EXTPERM'][] = 'frontedit[]';

if($REX["REDAXO"])
	$I18N->appendFile($REX['INCLUDE_PATH'].'/addons/frontedit/lang/');


/*

**** PRINZIP

- Frontedit gibt Inhalte dynamisch aus
- Vor jedem Slice wird HTML eingeschoben
-- im HTML Edit Button + Stylesheetkrams
-- add/edit/delete
	-- Im Header der Seite noch allgemeine Infos, wie aktivieren und deaktivieren, namen, Logout etc
	-- jquery vom kern einbinden

- rex_version noch mit einbauen
- achtung - spalten beachten
- verschieben von slices ?

- delete mit extra abfrage
	-> direkt - meldung über frontedit
- edit
	-> greybox popup, iframe mit nur diesem einen slice, eigener style und dann speichern
- add
	-> greybox mit diesem einen slice
	




- auch für navigation einbauen
- +/-/edit irgnedwie rein ?!?! spunkte einbauen, irgendein platzhalter oder funktion rein




*/

$REX['ADDON']['frontedit']['css_included'] = FALSE;

if(!$REX["REDAXO"])
{

	if(!isset($REX["LOGIN"]) || !is_object($REX["LOGIN"]) && !$REX["REDAXO"])
	{
		// eingeloggt und im frontend unterwegs
		// - noch prüfen ob frontend gewollt und aktiv
		
		$REX['ADDON']['frontedit']['MODULES'] = array();
		$gm = new rex_sql();
		$gm->setQuery('select * from rex_module');
		foreach($gm->getArray() as $m)
		{
			$REX['ADDON']['frontedit']['MODULES'][$m['id']] = htmlspecialchars($m['name']);
		}
	}

	// ***** an EPs andocken
	rex_register_extension('SLICE_SHOW', 'rex_frontedit_showSlice');
	function rex_frontedit_showSlice($params)
	{
		global $REX;
		
		if(!isset($REX["LOGIN"]) || !is_object($REX["LOGIN"]))
			return;
		
		// Benutzer ist eingeloggt
		
		if (!$REX['ADDON']['frontedit']['css_included'])
		{
			$REX['ADDON']['frontedit']['css_included'] = TRUE;
			echo '<div class="frontedit_header">Name: '.$REX["LOGIN"]->USER->getValue("name").'</div>';
			echo '<link rel="stylesheet" type="text/css" href="files/addons/frontedit/frontedit.css" media="screen" />';

			// http://fancy.klade.lv/howto
			echo '<link rel="stylesheet" type="text/css" href="files/addons/frontedit/jquery.fancybox.css" media="screen" />';
			echo '<script type="text/javascript" src="files/addons/frontedit/jquery-1.3.2.min.js"></script>';
			echo '<script type="text/javascript" src="files/addons/frontedit/jquery.fancybox-1.2.1.pack.js"></script>';
			
			?>
			<script type="text/javascript">

$(document).ready(function() { 

	/* This is basic - uses default settings */ 
	$("a#single_image").fancybox(); 
	
	/* Using custom settings */ 
	
	$("a#inline").fancybox({ 
		'hideOnContentClick': true 
		}); 
		
	$("a.group").fancybox({ 
		'zoomSpeedIn': 600, 
		'zoomSpeedOut': 600, 
		'overlayShow': false,
		'frameWidth': 780,
		'frameHeight': 400,
		}); 
		
}); 




  </script>
			
			
			<?php
		}

		// TODO:
		// I18N
		// Rechte prŸfen
		// Name des Moduls			
		
		$return = '';

		$return .= '<div class="frontedit_slice">';
		
		/*
		echo '<pre>';
		var_dump($params);
		echo '</pre>';
		*/
		
		$link_edit = 'redaxo/index.php?page=content&'.
		'article_id='.$params['article_id'].
		'&mode=edit'.
		'&clang=0'.
		'&ctype='.$params['ctype'].
		'&frontend_css=1'.
		'&slice_id='.$params['slice_id'].
		'&function=edit'.
		'&rex_set_version=0'.
		'&iframe';

		$return .= '<div class="frontedit_slice_header">';
		$return .= '<a class="group" href="'.$link_edit.'">Edit Slice</a>';
		$return .= ' | <a href="">Add Slice</a>';
		$return .= ' | ID: '.$params["slice_id"];
		$return .= ' | Modulname: '.$REX['ADDON']['frontedit']['MODULES'][$params["module_id"]];
		$return .= ' | <a href="">Delete Slice</a>';
		$return .= '</div>';

		
		
		$return .= '<div class="frontedit_slice_body">';
		$return .= $params["subject"];
		$return .= '<div style="clear:both;"></div></div>';
		
		$return .= '<div style="clear:both;"></div></div>';
	
		return $return;
	}
	
	
	// ***** an EPs andocken
	rex_register_extension('ART_INIT', 'rex_frontedit_initArticle');
	function rex_frontedit_initArticle($params)
	{
		global $REX;
	
		if(!isset($_SESSION))
		  session_start();
	
		$REX["LOGIN"] = new rex_backend_login($REX['TABLE_PREFIX'] .'user');
		if ($REX['PSWFUNC'] != '')
		  $REX['LOGIN']->setPasswordFunction($REX['PSWFUNC']);
	
		if ($REX["LOGIN"]->checkLogin() !== true)
		{
			unset($REX["LOGIN"]);
			return;
		}
		
		$REX["USER"] = &$REX["LOGIN"]->USER;
  	
	    // $params["article"]->setSliceRevision($version);
	  if(is_a($params["article"], 'rex_article'))
	  {
		  $params["article"]->getContentAsQuery();
	  }
		$params["article"]->setEval(TRUE);
	

	}
	
	
	
	
}


if($REX["REDAXO"])
{
	$fc = rex_request("frontend_css","int",0);
	if($fc == 1)
	{
		// backend css einbinden, so dass beim iframe aufruf aus dem frontend
		// die unnoetigen backendbereiche ausgeblendet werden
		
		// wenn edit speichern
		if(1==2)
		{
			// javascript um fenster zu schliessen
		}
		
		// wenn edit übernehmen. so belassen wir bisher
		
		function rex_frontend_addCSS($params)
		{
		    echo "\n".'<link rel="stylesheet" type="text/css" href="../files/addons/frontedit/frontedit_be.css" media="screen" />';
		}
		rex_register_extension('PAGE_HEADER', 'rex_frontend_addCSS');

		// nach forms und links suchen und frontend_css=1 setzen
		
    function rex_frontend_addFrontendCSSLink($params)
    {
      $suchmuster = array();
      $ersetzungen = array();
      
      $suchmuster[0] = '#(href)=[\"\'](.*)[\"\']#Um';
      // $ersetzungen[0] = 'href="\1\2\3&frontend_css=1"'; // frontedit_css=1

      $suchmuster[1] = '#(action)=[\"\'](.*)[\"\']#Um';
      // $ersetzungen[1] = 'action="\1?frontend_css=1"'; // frontedit_css=1
      
      $suchmuster[2] = '#(<form)(.*)(</form>)#Um';
      
      
      function rex_frontend_FELinkreplace($t)
      {
      	$r = "";
        switch($t[1])
        {
          case("<form"):
            $r = '<form'.$t[2].$t[3];
            break;
          case("href"):
            $r = 'href=';
          case("href"):
            $r = 'href=';
          default:
          	if(strpos($t[2],"?") === FALSE)
		          $r .= $t[2].'?&frontend_css=1';
		        else
		          $r .= $t[2].'&frontend_css=1';
        }
      	
        return $r;
      }
      $params["subject"] = preg_replace_callback($suchmuster,"rex_frontend_FELinkreplace",$params["subject"]);
      return $params["subject"];
    }
    rex_register_extension('OUTPUT_FILTER', 'rex_frontend_addFrontendCSSLink');
		
	
	
	
	}
}











