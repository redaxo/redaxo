<?php

$SF = true;

$table = "rex_com_comment";
$bezeichner = "Artikelkommentar";
$csuchfelder = array("comment");

$func = rex_request("func","string","");

//------------------------------> Hinzufügen

if($func == "add")
{

	echo $back_to_overview;

	$mita = new rexform;
	$mita->setWidth(770);
	$mita->setLabelWidth(160);
	$mita->setTablename($table);
	$oid = rex_request("oid","int",0);
	$mita->setFormtype("add");
	$mita->setFormheader('
		<input type=hidden name=page value="'.$page.'" / />
		<input type=hidden name=subpage value="'.$subpage.'" />
		<input type=hidden name=func value="'.$func.'" />
		');
	$mita->setShowFormAlways(false);
	$mita->setValue("subline","$bezeichner erstellen" ,"left",0);

	$mita->setValue("text","user_id","user_id",0);
	$mita->setValue("text","article_id","article_id",0);
	$mita->setValue("textarea","comment","comment",0);
	$mita->setValue("text","create_datetime","create_datetime",0);
	$mita->setValue("text","status","status",0);



	echo $mita->showForm();

	if (!$mita->form_show)
	{
		$func = "";
	}
	
}



//------------------------------> Editieren
if($func == "edit")
{
	
	echo $back_to_overview;
	
	$mita = new rexform;
	
	$mita->setWidth(770);
	$mita->setLabelWidth(160);
	$mita->setTablename($table);		
	$mita->setFormtype("edit", "id='".$oid."'", "Nachricht wurde nicht gefunden");

	$mita->setFormheader('
		<input type="hidden" name="page" value="'.$page.'" />
		<input type="hidden" name="subpage" value="'.$subpage.'" />
		<input type="hidden" name="func" value="'.$func.'" />
		<input type="hidden" name="oid" value="'.$oid.'" />
		');

	$mita->setShowFormAlways(false);				
	$mita->setValue("subline","$bezeichner edieren" ,"left",0);

	$mita->setValue("text","user_id","user_id",0);
	$mita->setValue("text","article_id","article_id",0);
	$mita->setValue("textarea","comment","comment",0);
	$mita->setValue("text","create_datetime","create_datetime",0);
	$mita->setValue("text","status","status",0);

	echo $mita->showForm();

	if (!$mita->form_show)
	{
		$func = "";
	}
	
}

//------------------------------> Löschen
if($func == "delete")
{
	$query = "delete from $table where id='".$oid."' ";
	$delsql = new rex_sql;
	$delsql->debugsql=0;
	$delsql->setQuery($query);
	$func = "";
}



//------------------------------> Liste
if($func == ""){
	
	
	/** Suche  **/
	$add_sql = "";
	$link	= "";
	
	// ADD
	echo '<table class="rex-table"><tr><td><a href="index.php?page='.$page.'&subpage='.$subpage.'&func=add"><b>+ '.$bezeichner.' hinzufügen</b></a></td></tr></table><br />';
	
	$sql = "select * from $table ".$add_sql;
	
	//echo $sql;
	
	$mit = new rexlist;
	$mit->setQuery($sql);
	$mit->setList(50);
	$mit->setGlobalLink("index.php?page=".$page."&subpage=".$subpage."".$link."&next=");

	$mit->setValue("id","id");
	$mit->setLink("index.php?page=".$page."&subpage=".$subpage."&func=edit&oid=","id");

	$mit->setValue("article_id","article_id");
	$mit->setValueOrder(1);

	$mit->setValue("user_id","user_id");
	$mit->setLink("index.php?page=".$page."&subpage=".$subpage."&func=edit&oid=","id");
	$mit->setValueOrder(1);

	$mit->setValue("status","status");

	$mit->setValue("editieren","");
	$mit->setFormat("ifempty", "editieren");
	$mit->setLink("index.php?page=".$page."&subpage=".$subpage."&func=edit&oid=","id");
	
	$mit->setValue("löschen","");
	$mit->setFormat("ifempty", "löschen");
	$mit->setFormat("link","index.php?page=".$page."&subpage=".$subpage."&func=delete&oid=","id","", " onclick=\"return confirm('sicher löschen ?');\"");	
	
	if (isset($FORM["ordername"]) && isset($FORM["ordertype"])) $mit->setOrder($FORM["ordername"],$FORM["ordertype"]);

	$next = rex_request("next","int","0");
	echo $mit->showall($next);
	
	echo "<br />";

}


?>