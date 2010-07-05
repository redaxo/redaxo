<?php

/*
    var $Host     = "localhost";
    var $Mailer   = "smtp";
*/

$error = "";



$method = rex_request("method","string");
$method_all = rex_request("method_all","string","");

// -------- E-Mail Typ / ob REDAXO oder XFORM

$nl_id = rex_request("nl_id","string");
if ($nl_id == "") 
  $nl_id = date("YmdHi");
  
$nl_type = rex_request("nl_type","string");
if($nl_type != "xform")
  $nl_type = "redaxo";


// -------- REDAXO artikel

$redaxo_nl_article_id = rex_request("redaxo_nl_article_id","int");
$redaxo_nl_article_name = "";
if($redaxo_nl_article_id > 0 && $m = OOArticle::getArticleById($redaxo_nl_article_id))
	$redaxo_nl_article_name = $m->getName();
else
	$redaxo_nl_article_id = 0;	
$redaxo_nl_from_email = rex_request("redaxo_nl_from_email","string");
$redaxo_nl_from_name = rex_request("redaxo_nl_from_name","string");
$redaxo_nl_subject = rex_request("redaxo_nl_subject","string");


// -------- xform email template

$xform_nl_tpl = "";
$xform_nl_tpl_tmp = rex_request("xform_nl_tpl","string");
$xform_nl_sql = new rex_sql;
$xform_nl_sql->setQuery("select * from rex_xform_email_template");
$xform_nl_tpls = $xform_nl_sql->getArray();

$xform_nl_select = new rex_select;
$xform_nl_select->setName("xform_nl_tpl");
foreach($xform_nl_tpls as $tpl)
{
	$xform_nl_select->addOption($tpl["name"],$tpl["name"]);
	if($xform_nl_tpl_tmp == $tpl["name"])
	{
		$xform_nl_tpl = $tpl;
		$xform_nl_select->setSelected($tpl["name"]);	
	}
}


// -------- Testuser ID

$test_user_id = rex_request("test_user_id","int",0);
$test_user = array();
$gu = new rex_sql();
$gu->setQuery('select * from rex_com_user where id='.$test_user_id);
$test_users = $gu->getArray();

if(count($test_users)!=1)
  $test_user_id = 0;
else
  $test_user = $test_users[0];






$info = array();
$error = array();

$send = FALSE;

// -------------------------------- Prüfen der Daten

if($method != "")
{
	if($nl_type == "xform")	
	{
		// xform
		if($xform_nl_tpl != "")
		{
			$nl_from_email = $xform_nl_tpl['mail_from'];
			$nl_from_name = $xform_nl_tpl['mail_from_name'];
			$nl_subject = $xform_nl_tpl['subject'];
			$nl_body_text = $xform_nl_tpl['body'];
			$nl_body_html = "";
			$send = TRUE;
		}else
		{
			$error[] = "Leider gibt es dieses Template nicht";
		}
	
	}else
	{
		// redaxo
		$nl_from_email = $redaxo_nl_from_email;
		$nl_from_name = $redaxo_nl_from_name;
		$nl_subject = $redaxo_nl_subject;

		if($nl_from_email == "" || $nl_from_name == "" || $nl_subject == "" || $redaxo_nl_article_id == 0)
		{
			$error[] = "Bitte prüfen Sie ob alle Infos eingetragen sind";
		}else
		{
					
			$tmp_redaxo = $REX['REDAXO'];
		
			 // ***** HTML VERSION KOMPLETT
			$REX['REDAXO'] = true;
			$REX_ARTICLE = new rex_article($redaxo_nl_article_id,0);
			$REX_ARTICLE->setEval(TRUE);
			$REX_ARTICLE->getContentAsQuery(TRUE);
			$REX['ADDON']['NEWSLETTER_TEXT'] = FALSE;
			$nl_body_html = $REX_ARTICLE->getArticleTemplate();
		
			// ***** TEXT VERSION
			$REX['REDAXO'] = true;
			$REX_ARTICLE = new rex_article($redaxo_nl_article_id,0);
			$REX_ARTICLE->setEval(TRUE);
			$REX_ARTICLE->getContentAsQuery(TRUE);
			$REX['ADDON']['NEWSLETTER_TEXT'] = TRUE; // FILTERN VERSION KOMPLETT
			$nl_body_text = $REX_ARTICLE->getArticleTemplate(); // Vielleicht auf artikel umbauen
			$nl_body_text = str_replace("<br />","<br />",$nl_body_text);
			$nl_body_text = str_replace("<p>","\n\n</p>",$nl_body_text);
			$nl_body_text = str_replace("<ul>","\n\n</ul>",$nl_body_text);
			$nl_body_text = preg_replace("#(\<)(.*)(\>)#imsU", "",  $nl_body_text);
			$nl_body_text = html_entity_decode($nl_body_text);
		
			$REX['REDAXO'] = $tmp_redaxo;
		
			$send = TRUE;
		}
	
	}

}





// ---------- Testversand

if($method == "start" && $method_all != "all" && count($error) == 0 && $send)
{
	if($test_user_id == 0)
	{
		$error[] = "User existiert nicht";
	}else
	{
		if(rex_newsletter_sendmail($test_user, $nl_from_email, $nl_from_name, $nl_subject, $nl_body_text, $nl_body_html))
		{
			$info[] = "Testmail wurde rausgeschickt. <br />Bitte überprüfen Sie ob die E-Mail ankommt und alles passt und schicken dann den kompletten Newsletter raus.";
		}else
		{
			$error[] = "Testmail ist fehlgeschlagen!";
		}
	
	}
}





// ---------- Versand an alle
if($method == "start" && $method_all == "all" && count($error) == 0 && $send)
{
		$nl = new rex_sql;
		// $nl->debugsql = 1;
		$nl->setQuery('select * from rex_com_user where newsletter_last_id<>"'.$nl_id.'" and email<>"" and newsletter=1 LIMIT 50');
		
		if($nl->getRows()>0)
		{
			$i = "".date("H:i:s")."h Bitte noch nicht abbrechen. Automatischer Reload. Es werden noch weitere E-Mails versendet";
			?><script>
			function win_reload(){ window.location.reload(); }
			setTimeout("win_reload()",5000); // Millisekunden 1000 = 1 Sek * 80
			</script><?php
			$i .= "<br />An folgende E-Mails wurde der Newsletter versendet: ";

			$up = new rex_sql;
			foreach($nl->getArray() as $userinfo)
			{
				$i .= ", ".$userinfo["email"];
				$up->setQuery('update rex_com_user set newsletter_last_id="'.$nl_id.'" where id='.$userinfo["id"]);
				$r = rex_newsletter_sendmail($userinfo, $nl_from_email, $nl_from_name, $nl_subject, $nl_body_text, $nl_body_html);
				$nl->next();	
			}

			$info[] = $i;
		}else
		{
			$info[] = "Alle eMails wurden verschickt";
		}

}





// ---------- Fehlermeldungen

if (count($error)>0)
	foreach($error as $e)
		echo rex_warning($e);		

if (count($info)>0)
	foreach($info as $i)
		echo rex_info($i);

?>



<table class="rex-table" cellpadding="5" cellspacing="1">

	<form action="index.php" method="get" name="REX_FORM">
	<input type="hidden" name="page" value="community" />
	<input type="hidden" name="subpage" value="plugin.newsletter" />
	<input type="hidden" name="method" value="start" />

	<tr>
		<th class="rex-icon">&nbsp;</th>
		<th colspan="2"><b>Newslettertyp auswaehlen:</b></th>
	</tr>
	<tr>
		<td class="rex-icon"><input type="radio" name="nl_type" id="nl_type_redaxo" value="article" <?php if($nl_type != "xform") echo 'checked="checked"'; ?> /></td>
		<td width="200"><label for="nl_type_redaxo">REDAXO Artikel:</label></td>
		<td>
			<div class="rex-wdgt">
			<div class="rex-wdgt-lnk">
			<p>
				<input type="hidden" name="redaxo_nl_article_id" id="LINK_1" value="<?php echo $redaxo_nl_article_id; ?>" />
				<input type="text" size="30" name="redaxo_nl_article_name" value="<?php echo stripslashes(htmlspecialchars($redaxo_nl_article_name)); ?>" id="LINK_1_NAME" readonly="readonly" />
				<a href="#" onclick="openLinkMap('LINK_1', '&clang=0');return false;" tabindex="23"><img src="media/file_open.gif" width="16" height="16" alt="Open Linkmap" title="Open Linkmap" /></a>
				<a href="#" onclick="deleteREXLink(1);return false;" tabindex="24"><img src="media/file_del.gif" width="16" height="16" title="Remove Selection" alt="Remove Selection" /></a>
			</p>
			</div>
			</div>
		</td>
	</tr>

	<tr>
		<td class=rex-icon>&nbsp;</td>
		<td>Absende-eMail:</td>
		<td><input type="text" size="30" name="redaxo_nl_from_email" value="<?php echo stripslashes(htmlspecialchars($redaxo_nl_from_email)); ?>" class="inp100" /></td>
	</tr>
	<tr>
		<td class=rex-icon>&nbsp;</td>
		<td>Absende-Name:</td>
		<td><input type="text" size="30" name="redaxo_nl_from_name" value="<?php echo stripslashes(htmlspecialchars($redaxo_nl_from_name)); ?>" class="inp100" /></td>
	</tr>
	<tr>
		<td class=rex-icon>&nbsp;</td>
		<td>Betreff/Subject:</td>
		<td><input type="text" size="30" name="redaxo_nl_subject" value="<?php echo stripslashes(htmlspecialchars($redaxo_nl_subject)); ?>" />
		<br />[ Auch Platzhalter m&ouml;glich z.B. ###email### ]
		</td>
	</tr>
	
	<tr>
		<td class="rex-icon"><input type="radio" name="nl_type" id="nl_type_xform" value="xform" <?php if($nl_type == "xform") echo 'checked="checked"'; ?> /></td>
		<td width="200"><label for="nl_type_xform">XForm E-Mail Template:</label></td>
		<td><?php echo $xform_nl_select->get(); ?></td>
	</tr>

	<tr>
		<th class="rex-icon">&nbsp;</th>
		<th colspan="2"><b>Newsletterempfaenger:</b></th>
	</tr>

	<tr>
		<td class=rex-icon>&nbsp;</td>
		<td>NewsletterID:</td>
		<td><input type="text" size="30" name="nl_id" value="<?php echo stripslashes(htmlspecialchars($nl_id)); ?>" class="inp100" />
		 <br />[ wird nur an User geschickt, die diese ID noch nicht gesetzt haben
		 <br />Diese Newsletter ID wird bei jedem Versand an den entsprechenden User gesetzt ]</td>
	</tr>
	
	<tr>
		<th class=rex-icon>&nbsp;</th>
		<th colspan=2><b>Daten f&uuml;r Testmail eingeben:</b></th>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>Testuser ID:</td>
		<td><input type="text" size="30" name="test_user_id" value="<?php echo stripslashes(htmlspecialchars($test_user_id)); ?>" /></td>
	</tr>
	<?php if ($method == "start" && count($error) == 0) { ?>
	<tr>
		<td>&nbsp;</td>
		<td>Testmail ok ? Dann H&auml;kchen setzen <br>und Newsletter wird abgeschickt.</td>
		<td><input type="checkbox" name="method_all" value="all" /></td>
	</tr>
	<?php } ?>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><input type="submit" value="Mail/s verschicken" class="submit" /></td>
	</tr>
	</form>
</table>


