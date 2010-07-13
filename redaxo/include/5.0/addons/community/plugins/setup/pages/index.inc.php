<?php

$func = rex_request("func","string","");
$request_module = rex_request("module","string","");
$request_template = rex_request("template","string","");
$request_email = rex_request("email","string","");
$request_update = rex_request("update","int","");




// ***** ADD MDL
if ($func == "ids")
{
	$content = "";
	$file = $REX['INCLUDE_PATH']."/addons/community/plugins/setup/config.inc.php";
	$i=0;
	foreach($REX["ADDON"]["community"]["plugins"]["setup"]["ids"] as $k)
	{
		$i++;
		$v = (int) $_REQUEST["LINK"][$i];
		$content .= '$REX["ADDON"]["COMMUNITY_VARS"]["'.$k.'"] = "'.$v.'";'."\n";
		$REX["ADDON"]["COMMUNITY_VARS"][$k] = $v;
	}
	rex_replace_dynamic_contents($file, $content);
	
	echo rex_info('IDs/Konstanten wurden aktualisiert.');
}







// ***** ADD MDL
if ($func == "module" && $request_module != "")
{

	$modules = $REX["ADDON"]["community"]["plugins"]["setup"]["modules"];
	foreach($modules as $module)
	{
		if ($request_module == $module[0].".".$module[1])
		{
		
			$in = rex_get_file_contents($REX["INCLUDE_PATH"]."/addons/community/plugins/".$module[0]."/modules/".$module[1]."_in.module");
			$out = rex_get_file_contents($REX["INCLUDE_PATH"]."/addons/community/plugins/".$module[0]."/modules/".$module[1]."_out.module");
			
			$in = str_replace("\r","\n",$in); $in = str_replace("\n\n","\n",$in);
			$out = str_replace("\r","\n",$out); $out = str_replace("\n\n","\n",$out);
			
			$mi = new rex_sql;
			// $mi->debugsql = 1;
			$mi->setTable("rex_module");
			$mi->setValue("name",$module[2]);
			$mi->setValue("eingabe",addslashes($in));
			$mi->setValue("ausgabe",addslashes($out));
			
			if ($request_update == 1)
			{
				$a = new rex_sql;
				// $a->debugsql = 1;
				$a->setQuery('select * from rex_module where name="'.addslashes($module[2]).'" LIMIT 2');
				if ($a->getRows()==1)
				{
					$mi->setWhere('id='.$a->getValue("id").'');
					$mi->update();
					echo rex_info('Modul "'.htmlspecialchars($module[2]).'" wurde aktualisiert.');
				
				}else
				{
					echo rex_warning('Modul "'.htmlspecialchars($module[2]).'" konnte nicht aktualisiert werden, da diese nicht gefunden wurde.');
				}
			}else
			{
				$mi->insert();
				echo rex_info('Modul "'.htmlspecialchars($module[2]).'" wurde angelegt.');
			}

			rex_generateAll();

		}
	}

}

// ***** ADD TPL
if ($func == "template" && $request_template != "")
{

	$templates = $REX["ADDON"]["community"]["plugins"]["setup"]["templates"];
	foreach($templates as $template)
	{
		if ($request_template == $template[0].".".$template[1])
		{
		
			$content = rex_get_file_contents($REX["INCLUDE_PATH"]."/addons/community/plugins/".$template[0]."/templates/".$template[1].".template");
			$active = (int) $template[3];
			
			$content = str_replace("\r","\n",$content); $content = str_replace("\n\n","\n",$content);

			
			$mi = new rex_sql;
			// $mi->debugsql = 1;
			$mi->setTable("rex_template");
			$mi->setValue("name",addslashes($template[2]));
			$mi->setValue("active",$active);
			$mi->setValue("content",addslashes($content));
			$mi->setValue("attributes",addslashes('a:1:{s:5:"ctype";a:0:{}}'));
			$mi->addGlobalCreateFields();
		
			if ($request_update == 1)
			{
				$a = new rex_sql;
				// $a->debugsql = 1;
				$a->setQuery('select * from rex_template where name="'.addslashes($template[2]).'" LIMIT 2');
				if ($a->getRows()==1)
				{
					$mi->setWhere('id='.$a->getValue("id").'');
					$mi->update();
					echo rex_info('Template "'.htmlspecialchars($template[2]).'" wurde aktualisiert.');
				
				}else
				{
					echo rex_warning('Template "'.htmlspecialchars($template[2]).'" konnte nicht aktualisiert werden, da diese nicht gefunden wurde.');
				}
			}else
			{
				$mi->insert();
				echo rex_info('Template "'.htmlspecialchars($template[2]).'" wurde angelegt.');
			}
		
			rex_deleteDir($REX['INCLUDE_PATH']."/generated/templates", 0);
		
		}
	}

}



// ***** ADD EMAIL
if ($func == "email" && $request_email != "")
{

	$emails = $REX["ADDON"]["community"]["plugins"]["setup"]["emails"];
	foreach($emails as $email)
	{
		if ($request_email == $email[0].".".$email[1])
		{
		
			$body = rex_get_file_contents($REX["INCLUDE_PATH"]."/addons/community/plugins/".$email[0]."/emails/".$email[1].".email");
			
			$body = str_replace("\r","\n",$body); 
			$body = str_replace("\n\n","\n",$body);
			
			foreach($REX["ADDON"]["COMMUNITY_VARS"] as $k => $v)
			{
				$body = str_replace('###'.$k.'###',$v,$body);
			}
			$body = str_replace('###REXCOM_SERVER###',$REX['SERVER'],$body);
			$body = str_replace('###REXCOM_EMAIL###',$REX['ERROR_EMAIL'],$body);
			
			
			$mi = new rex_sql;
			// $mi->debugsql = 1;
			$mi->setTable("rex_xform_email_template");
			$mi->setValue("name",addslashes($email[2]));
			$mi->setValue("subject",addslashes($email[3]));
			$mi->setValue("mail_from",addslashes($email[4]));
			$mi->setValue("mail_from_name",addslashes($email[5]));
			$mi->setValue("body",addslashes($body));

			if ($request_update == 1)
			{
				$a = new rex_sql;
				// $a->debugsql = 1;
				$a->setQuery('select * from rex_xform_email_template where name="'.addslashes($email[2]).'" LIMIT 2');
				if ($a->getRows()==1)
				{
					$mi->setWhere('id='.$a->getValue("id").'');
					$mi->update();
					echo rex_info('EMail "'.htmlspecialchars($email[2]).'" wurde aktualisiert.');
				
				}else
				{
					echo rex_warning('EMail "'.htmlspecialchars($email[2]).'" konnte nicht aktualisiert werden, da diese nicht gefunden wurde.');
				}
			}else
			{
				$mi->insert();
				echo rex_info('EMail "'.htmlspecialchars($email[2]).'" wurde angelegt.');
			}
		}
	}

}

// ********** / FUNKTIONEN: Start Listen

?>

<div class="rex-area">

<h3 class="rex-hl2">Setup</h3>



<h3 class="rex-hl2">Module</h3>
<div class="rex-area-content">
<ul style="margin-left:20px;">
<?php
$modules = $REX["ADDON"]["community"]["plugins"]["setup"]["modules"];
foreach($modules as $module)
{
	$link = 'index.php?page=community&subpage='.$subpage.'&func=module&module='.urlencode($module[0]).'.'.urlencode($module[1]);
	$g = new rex_sql;
	// $g->debugsql = 1;
	$g->setQuery('select * from rex_module where name="'.addslashes($module[2]).'" LIMIT 1');
	if ($g->getRows()==1) 
		echo '<li>'.htmlspecialchars($module[2]).' - [ schon vorhanden | <a href="'.$link.'&update=1">aktualisieren</a> ]</li>';
	else 
		echo '<li><a href="'.$link.'">'.htmlspecialchars($module[2]).'</a></li>';
}
?>
</ul>
</div>



<h3 class="rex-hl2">Templates</h3>
<div class="rex-area-content">
<ul style="margin-left:20px;">
<?php
$templates = $REX["ADDON"]["community"]["plugins"]["setup"]["templates"];
foreach($templates as $template)
{
	$link = 'index.php?page=community&subpage='.$subpage.'&func=template&template='.urlencode($template[0]).'.'.urlencode($template[1]);
	$g = new rex_sql;
	// $g->debugsql = 1;
	$g->setQuery('select * from rex_template where name="'.addslashes($template[2]).'" LIMIT 1');
	if ($g->getRows()==1) 
		echo '<li>'.htmlspecialchars($template[2]).' - [ schon vorhanden | <a href="'.$link.'&update=1">aktualisieren</a> ]</li>';
	else 
		echo '<li><a href="'.$link.'">'.htmlspecialchars($template[2]).'</a></li>';
}
?>
</ul>
</div>


<h3 class="rex-hl2">EMails</h3>
<div class="rex-area-content">
<ul style="margin-left:20px;">
<?php
$emails = $REX["ADDON"]["community"]["plugins"]["setup"]["emails"];
foreach($emails as $email)
{
	$link = 'index.php?page=community&subpage='.$subpage.'&func=email&email='.urlencode($email[0]).'.'.urlencode($email[1]);
	$g = new rex_sql;
	// $g->debugsql = 1;
	$g->setQuery('select * from rex_xform_email_template where name="'.addslashes($email[2]).'" LIMIT 1');
	if ($g->getRows()==1) 
		echo '<li>'.htmlspecialchars($email[2]).' - [ schon vorhanden | <a href="'.$link.'&update=1">aktualisieren</a> ]</li>';
	else 
		echo '<li><a href="'.$link.'">'.htmlspecialchars($email[2]).'</a></li>';
}
?>
</ul>
</div>



<h3 class="rex-hl2">IDs</h3>
<div class="rex-area-content">
<form action="index.php" method="post" />
<input type="hidden" name="page" value="community" />
<input type="hidden" name="subpage" value="plugin.setup" />
<input type="hidden" name="func" value="ids" />
<?php

$ids = $REX["ADDON"]["community"]["plugins"]["setup"]["ids"];
$i=0;
foreach($ids as $v)
{
	$i++;
	$name = "";
	$val = "0";
	if(isset($REX["ADDON"]["COMMUNITY_VARS"][$v]))
	  $val = $REX["ADDON"]["COMMUNITY_VARS"][$v];
	
	if ($val != "0")
	  if ($a = OOArticle::getArticleById($val)) 
	    $name = $a->getName();	
	?>
	
	<div class="pluginbox" style="width:350px;float:left; ">
	<p><?php echo $v; ?></p>
	
	<div class="rex-wdgt">
		<div class="rex-wdgt-lnk">

          <p class="rex-wdgt-fld">
  			<input type="hidden" name="LINK[<?php echo $i; ?>]" id="LINK_<?php echo $i; ?>" value="<?php echo $val; ?>" />
  			<input type="text" size="30" name="LINK_NAME[<?php echo $i; ?>]" value="<?php echo htmlspecialchars($name); ?>" id="LINK_<?php echo $i; ?>_NAME" readonly="readonly" />
  		  </p>
          <p class="rex-wdgt-icons">
          	<a href="#" onclick="openLinkMap('LINK_<?php echo $i; ?>', '&clang=0&category_id=1');return false;"><img src="media/file_open.gif" width="16" height="16" alt="Open Linkmap" title="Open Linkmap" /></a>
 			<a href="#" onclick="deleteREXLink(<?php echo $i; ?>);return false;" tabindex="29"><img src="media/file_del.gif" width="16" height="16" title="Remove Selection" alt="Remove Selection" /></a>
 		  </p>
 		  <div class="rex-clearer"></div>

 		</div>
 	</div>
 	
 	</div>
	<?php
}

?><div class="rex-clearer"></div>
<input type="submit" value="abschicken" class="rex-form-submit submit" style="margin-top:20px;" />
</form>
</div>


</div>



