<?php

$SF = true;

$table = 'rex_com_user_field';
$table_user = 'rex_com_user';

$func = rex_request("func","string","");
$field_id = rex_request("field_id","int","");


$rep = "";
$UT = $REX["ADDON"]["community"]["ut"];
foreach($UT as $key => $value)
{
	if ($rep != "") $rep .= ";";
	$rep .= "$value=$key";
}

//------------------------------> Poll Anlegen|Editieren
if($func == "add" || $func == "edit")
{
	
	if($func == "edit")
		echo '<div class="rex-area"><h3 class="rex-hl2">Feld editieren</h3><div class="rex-area-content">';
	else
		echo '<div class="rex-area"><h3 class="rex-hl2">Feld hinzufügen</h3><div class="rex-area-content">';
		
	
	$form_data = "";
	
	// $form_data .= "\n"."html|<h1>Userfeld erstellen</h1>";
	
	$form_data .= "\n"."text|prior|Prior";
	$form_data .= "\n"."validate|notEmpty|prior|Bitte geben SIe die Priorität ein";

	$form_data .= "\n"."text|name|Bezeichnung";
	$form_data .= "\n"."validate|notEmpty|name|Bitte geben Sie den Namen ein";

	$form_data .= "\n"."text|userfield|Datenbankbezeichnung";
	$form_data .= "\n"."validate|notEmpty|userfield|Bitte geben Sie die userfield Bezeichnung ein";

	$form_data .= "\n".'select|type|Typ|'.$rep.'';

	$form_data .= "\n".'html|<p><label>Beispiele</label>';
	$form_data .= "\n".'html|<span style="display:block;float:left">INT * extra1=14<br />';
	$form_data .= "\n".'html|VARCHAR * extra1=255<br />';
	$form_data .= "\n".'html|TEXT * <br />';
	$form_data .= "\n".'html|PASSWORD * extra1=md5<br />';
	$form_data .= "\n".'html|SELECT * extra1 offline=0;online=1<br />';
	$form_data .= "\n".'html|BOOL *<br />';
	$form_data .= "\n".'html|FLOAT(10,7) für Positionen wir lat und lng *<br />';
  $form_data .= "\n".'html|REDAXO Medialist<br />';
	$form_data .= "\n".'html|REDAXO Mediafile</span></p>';
  
	$form_data .= "\n"."text|extra1|extra1";
	
	$form_data .= "\n"."checkbox|inlist|Erscheint in Userliste";
	$form_data .= "\n"."checkbox|editable|Editierbar";
	$form_data .= "\n"."checkbox|mandatory|Pflichtfeld";
	$form_data .= "\n"."checkbox|unique|Unique";
	$form_data .= "\n"."text|defaultvalue|Defaultwert";
	
	$form_data .= "\n".'hidden|page|'.$page.'|REQUEST|no_db'."\n".'hidden|subpage|'.$subpage.'|REQUEST|no_db';
	$form_data .= "\n".'hidden|func|'.$func.'|REQUEST|no_db';

	$xform = new rex_xform;
	// $xform->setDebug(TRUE);
	$xform->objparams["actions"][] = array("type" => "showtext","elements" => array("action","showtext",'','<p class="warning">Vielen Dank für die Aktualisierung</p>',"",),);
	$xform->setObjectparams("main_table",$table); // fŸr db speicherungen und unique abfragen

	if($func == "edit")
	{
		$form_data .= "\n".'hidden|field_id|'.$field_id.'|REQUEST|no_db';
		$xform->objparams["actions"][] = array("type" => "db","elements" => array("action","db",$table,"id=$field_id"),);
		$xform->setObjectparams("main_id","$field_id");
		$xform->setObjectparams("main_where","id=$field_id");
		$xform->setGetdata(true); // Datein vorher auslesen
	}elseif($func == "add")
	{
		$xform->objparams["actions"][] = array("type" => "db","elements" => array("action","db",$table),);
	}

	$xform->setFormData($form_data);
	echo $xform->getForm();

	echo '</div></div>';
	
	echo '<br />&nbsp;<br /><table cellpadding="5" class="rex-table"><tr><td><a href="index.php?page='.$page.'&amp;subpage='.$subpage.'"><b>&laquo; '.$I18N->msg('back_to_overview').'</b></a></td></tr></table>';
	
}

//------------------------------> löschen
if($func == "delete"){

	$gf = new rex_sql;
	$gf->setQuery("select * from $table where id='".$field_id."'");
	if ($gf->getRows()==1 && $gf->getValue("userfield")!= "id")
	{
		// feste felder - nicht loeschbar	
		if (in_array($gf->getValue("userfield"),$REX["ADDON"]["community"]["ff"]))
		{
			
			echo rex_warning('Das Feld "'.$gf->getValue("userfield").'" kann nicht gelöscht werden da es ein fester Bestandteil ist');
			$func = "";	
			
		}else
		{
			$query = "delete from $table where id='".$field_id."' ";
			$delsql = new rex_sql;
			$delsql->debugsql=0;
			$delsql->setQuery($query);
			$func = "";

			echo rex_info('Dae Feld "'.$gf->getValue("userfield").'" wurde gelöscht');

			$gf->setQuery("ALTER TABLE `$table_user` DROP `".$gf->getValue("userfield")."`");
			
		}
	}
}



//------------------------------> Userliste
if($func == ""){

	// ***** add 
	echo '<table cellpadding="5" class="rex-table"><tr><td><a href="index.php?page='.$page.'&amp;subpage='.$subpage.'&amp;func=add"><b>+ Feld anlegen</b></a></td></tr></table><br />';

	$ssql = new rex_sql();
	$sql = "select * from $table order by prior";
	
	$list = rex_list::factory($sql,30);
	$list->setColumnFormat('id', 'Id');
	$list->removeColumn('extra1');
	$list->removeColumn('extra2');
	$list->removeColumn('extra3');
	$list->addColumn('l&ouml;schen','l&ouml;schen');
	$list->setColumnParams("l&ouml;schen", array("field_id"=>"###id###","func"=>"delete"));
	// $list->setColumnParams("id", array("field_id"=>"###id###","func"=>"edit"));
	$list->setColumnParams("name", array("field_id"=>"###id###","func"=>"edit"));
	echo $list->get();


	/*
	$mit->setQuery($sql);
	$mit->setList(50);
	$mit->setGlobalLink("index.php?page=".$page."&subpage=".$subpage."".$link."&next=");
	$mit->setValue("prior","prior");
	$mit->setValue("id","id");
	$mit->setValue("Name","name");
	$mit->setValueOrder(1);
	$mit->setLink("index.php?page=".$page."&subpage=".$subpage."&func=edit&oid=","id");
	$mit->setValue("Userfield","userfield");

	$mit->setValue("Typ","type");
	$mit->setFormat("replace_value",$rep);

	$mit->setValue("In Übersicht","inlist");
	$mit->setFormat("replace_value","0|nein|1|ja");
	
	$mit->setValue("Editierbar","editable");
	$mit->setFormat("replace_value","0|nein|1|ja");
	
	$mit->setValue("Pflichtfeld","mandatory");
	$mit->setFormat("replace_value","0|nein|1|ja");

	$mit->setValue("löschen","");
	$mit->setFormat("ifempty", "- löschen");
	$mit->setFormat("link","index.php?page=".$page."&subpage=".$subpage."&func=delete&oid=","id",""," onclick=\"return confirm('sicher löschen ?');\"");	
	if (isset($FORM["ordername"]) && isset($FORM["ordertype"])) $mit->setOrder($FORM["ordername"],$FORM["ordertype"]);
	echo $mit->showall(@$next);
	*/
	
	$r = rex_com_checkFields($table, $table_user);
	if($r["status"] == 1)
		echo "<br />".rex_info($r["message"]);
	else
		if(is_array($r["message"]))
			foreach($r["message"] as $m)
				echo "<br />".rex_warning($m);
		else
			echo "<br />".rex_warning($m);
	

}