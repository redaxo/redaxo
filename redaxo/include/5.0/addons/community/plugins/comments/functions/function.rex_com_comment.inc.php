<?php

function rex_com_comment($type, $type_id, $link_params, $params = array())
{
	// es gibt verschiedene typen, z.B. article, slice, partner, news, event
	// und die entsprechenden id dazu
	// link_params sind für die urlgenerierung

	global $REX;


	// Hilfsvariablen
	$kk = $type.'-'.$type_id;
	$kkk = '#teaser-comment-comments-'.$kk;


	// Kommentar hinzufügen. Wird hier gestartet, damit die Kommentarliste aktuell ist,
	$addcomment = "";
	if(is_object($REX["COM_USER"]))
	{
		$addcomment .= 
			'<div id="teaser-addcomment-'.$kk.'" class="teaser-comment-info"><ul class="teaser-list">
			<li class="first">'.date("d. M. Y - H:i").'</li></ul></div>';
		$addcomment .= rex_com_comment_form($type,$type_id,$link_params);
	}else
	{
		$addcomment .= '<div id="teaser-addcomment-'.$kk.'" class="teaser-comment-info"><p>Zum kommentieren bitte einloggen</p></div>';
	}


	// $classOuter .= ' teaser-v2';
	$comment = '';
	$comment .= '<div class="commentbox">';

	$comments_sql = new rex_sql();
	// $comments_sql->debugsql = 1;
	$comments_sql->setQuery('select * from rex_com_comment 
		left join rex_com_user on rex_com_user.id=rex_com_comment.user_id 
		where 
			rex_com_comment.type="'.$type.'" and rex_com_comment.type_id='.$type_id.' order by rex_com_comment.create_datetime');
	$comments = $comments_sql->getArray();

	$comment .= '<p class="teaser-comment-comments"><a href="javascript:void(0);" onclick="if($(\''.$kkk.'\').is(\':hidden\')){$(\''.$kkk.'\').slideDown(1000);}else{$(\''.$kkk.'\').slideUp(1000);}">'.count($comments).' ';

	if(count($comments)==1)
		$comment .= 'Kommentar';
	else
		$comment .= 'Kommentare';

	$comment .= '</a></p>';

	$st = "display:none;";
	if($_REQUEST["FORM"]['form-'.$type.'-'.$type_id]['form-'.$type.'-'.$type_id.'send'] == 1 || isset($params["showall"]))
		$st = "display:block;";

	$comment .= '<div id="teaser-comment-comments-'.$kk.'" class="teaser-comments" style="'.$st.'">';
	
	foreach($comments as $c)
	{
	
		$image = '/comment.jpg';
		
		if($c["photo"] != "")
			$image = $c["photo"];

		$s = getimagesize($REX['HTDOCS_PATH'].'files/'.$image);
		
		$srcWidth = '50';
		$factor = $s[0] / $srcWidth;
		$srcHeight = ceil($s[1] / $factor);
		
		// Wichtig: Breite und Hoehe des Bildes muss wegen abgerundeter Ecken per Style uebergeben werden.
		$style = '';
		$style = ' style="width: '.$srcWidth.'px; height: '.$srcHeight.'px;"';
		
		$n = $c["login"];
		if($c["scout"])
			$n = $c["firstname"]." ".$c["name"]." [Scout]";
		
		$n = '<a class="popup-v2" href="'.rex_getUrl(48,'',array("user_id"=>$c["id"])).'&iframe">'.htmlspecialchars($n).'</a>';
		
		$comment .= '<div class="teaser-comment">
						<p class="img fl-lft"'.$style.'><span class="img"><img'.$style.' src="/index.php?rex_resize='.$srcWidth.'w__'.$image.'" title="Kommentar von" alt="Kommentar von" /></span></p>
						<div class="teaser-comment-content">
							<ul class="teaser-list"><li class="first"><strong>'.($n).'</strong></li><li>'.date('d. M. Y - H:i',$c["create_datetime"]).'</li></ul>
							<p>'.nl2br(htmlspecialchars($c["comment"])).'</p>
						</div>';

		if($c["rank"]>0)
		{
			// <a class="ranking-3" href="#">
			$comment .= '
						<div class="teaser-info">
						<ul class="ranking ranking-'.$c["rank"].'">
							<li class="first">1 Stern </li>
							<li>2 Sterne </li>
							<li>3 Sterne </li>
							<li>4 Sterne </li>
							<li>5 Sterne </li>
						</ul>
						</div>
					';
		}
		
		$comment .= '	
						<div class="clearer"></div>
					</div>';


	}

	$comment .= $addcomment;

	$comment .= '<div class="clearer"></div></div>';
	$comment .= '<div class="clearer"></div></div>';


	return $comment;

}



function rex_com_comment_form($type,$type_id,$link_params = "")
{

	global $REX;

	if(!is_object($REX["COM_USER"]))
		return;
	
	$table = 'rex_com_comment';

	$form_data  = '';
	$form_data .= "\n".'objparams|form_wrap|<div id="xform-comment" class="xform xform-comment">#</div>';

	$form_data .= "\n".'hidden|status|1|';
	
	$_REQUEST["type"] = $type;
	$_REQUEST["type_id"] = $type_id;
	
	$form_data .= "\n".'hidden|type|none|REQUEST|';
	$form_data .= "\n".'hidden|type_id|0|REQUEST|';
	$form_data .= "\n".'hidden|user_id|'.$REX['COM_USER']->USER->getValue('id');
	
	if(is_array($link_params))
	{
		foreach($link_params as $k => $v)
		{
			// echo "<br />******** $k 00 $v ";rex_com::debug($link_params);
			$form_data .= "\n".'hidden|'.$k.'|'.$v.'|REQUEST|no_db';
		}
	}
	
	$form_data .= "\n".'timestamp|create_datetime';
	$form_data .= "\n".'select|rank|Sterne:|Keine Angabe=;1 Stern=1;2 Sterne=2;3 Sterne=3;4 Sterne=4;5 Sterne=5|';
	$form_data .= "\n".'textarea|comment|Kommentar: *';
	$form_data .= "\n".'validate|empty|comment|Bitte gib einen Kommentar ein';

	$form_data .= "\n".'html|<p class="formnotice"><span>* Pflichtfelder</span></p>';

	$form_data .= "\n".'objparams|submit_btn_show|0';
	$form_data .= "\n".'objparams|form_anchor|teaser-addcomment-'.$type.'-'.$type_id;
	
	$form_data .= "\n".'html|<p class="formsubmit"><span class="form-element"><span class="form-element-i"><input class="submit" type="submit" name="submit" value="abschicken"  /></span></span></p>';
	$form_data .= "\n".'html|<div class="clearer"></div>';
	
	$form_data .= "\n".'validate|sunity_max_comment|user_id|'.$type.'|'.$type_id.'|'.$REX['COM_USER']->USER->getValue('id').'|3|'.(60*60*24).'|Es sind maximal 3 Einträge in 24 Stunden pro Meldung erlaubt';
	
	
	
	// $form_data = trim(str_replace("<br />","",rex_xform::unhtmlentities($form_data)));
	
	$xform = new rex_xform;
	$xform->setObjectparams("form_name",'form-'.$type.'-'.$type_id);

	// $xform->setDebug(TRUE);

	$xform->objparams["actions"][] = array("type" => "showtext","elements" => array("action","showtext",'','<div class="teaser-comment-info teaser-comment-thanks"><p>Vielen Dank für die Eintragung</p></div>',"",),);
	$xform->objparams["actions"][] = array("type" => "db", "elements" => array("action","db",$table),);

	$xform->objparams["actions"][] = array("type" => "com_comment_rank", "elements" => array("action",$type,$type_id),);

	$xform->setObjectparams("main_table",$table); // fŸr db speicherungen und unique abfragen
	
	$xform->setRedaxoVars();
	$xform->setFormData($form_data);
	
	return $xform->getForm();

}


