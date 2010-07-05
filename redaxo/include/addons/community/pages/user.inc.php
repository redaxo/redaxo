<?php

$SF = true;
$table = $REX['TABLE_PREFIX'].'com_user';
$table_field = $REX['TABLE_PREFIX'].'com_user_field';

$bezeichner = "User";

$func = rex_request("func","string","");
$page = rex_request("page","string","");
$subpage = rex_request("subpage","string","");
$user_id = rex_request("user_id","int");


//------------------------------
if($func == "add" || $func == "edit")
{
	
	if($func == "edit")
		echo '<div class="rex-area"><h3 class="rex-hl2">User editieren</h3><div class="rex-area-content">';
	else
		echo '<div class="rex-area"><h3 class="rex-hl2">User hinzufügen</h3><div class="rex-area-content">';
		
	// ***** Allgemeine BE Felder reinlegen
	$form_data = "\n".'hidden|page|'.$page.'|REQUEST|no_db'."\n".'hidden|subpage|'.$subpage.'|REQUEST|no_db'."\n".'hidden|func|'.$func.'|REQUEST|no_db';

	// ***** Felder auslesen und entsprechend des Typen festlegen.	
	$guf = new rex_sql;
	$guf->setQuery("select * from ".$table_field." where editable=1 order by prior");
	foreach($guf->getArray() as $key => $value)
	{
		$value["mandatory"] = (int) $value["mandatory"];
	  switch($value["type"])
		{
			case("3"):
				// bool
				$form_data .= "\n".'textarea|'.$value["userfield"].'|'.$value["name"];
				break;
			case("4"):
				// bool
				$form_data .= "\n".'password|'.$value["userfield"].'|'.$value["name"];
				break;
			case("5"):
				// select
				$form_data .= "\n".'select|'.$value["userfield"].'|'.$value["name"].'|'.$value["extra1"].'|';
				break;
			case("6"):
				// bool
				$form_data .= "\n".'checkbox|'.$value["userfield"].'|'.$value["name"];
				break;
			case("8"):
				// sql select
				if($value["mandatory"] != 1)
					$value["mandatory"] = "Keine Angabe";
				$form_data .= "\n".'select_single_sql|'.$value["userfield"].'|'.$value["name"].'|'.$value["mandatory"].'|'.$value["extra1"].'|';
				break;
			
			case("9"):
        // medialist
        $form_data .= "\n".'be_medialist|'.$value["userfield"].'|'.$value["name"];
        break;
        
      case("10"):
        // mediafile
        $form_data .= "\n".'be_mediapool|'.$value["userfield"].'|'.$value["name"].'|'.$value["extra1"].'|';
        break;
				
				
				
				
			default:
				// sonstige
				$form_data .= "\n".'text|'.$value["userfield"].'|'.$value["name"];
				break;
		}
		
		// Pflichtfelder festlegen
		
		if($value["mandatory"] == 1)
			$form_data .= "\n".'validate|empty|'.$value["userfield"].'|Bitte geben Sie im Feld "'.$value["name"].'" etwas ein.';

		$value["unique"] = (int) $value["unique"];
		if($value["unique"] == 1)
			$form_data .= "\n".'validate|unique|'.$value["userfield"].'|Der Wert im Feld "'.$value["name"].'" existiert bereits.';


	}

	$form_data = trim(str_replace("<br />","",rex_xform::unhtmlentities($form_data)));


	$xform = new rex_xform;
	// $xform->setDebug(TRUE);
	$xform->objparams["actions"][] = array("type" => "showtext","elements" => array("action","showtext",'','<p>Vielen Dank für die Eintragung</p>',"",),);
	$xform->setObjectparams("main_table",$table); // fŸr db speicherungen und unique abfragen

	if($func == "edit")
	{
		$form_data .= "\n".'hidden|user_id|'.$user_id.'|REQUEST|no_db';
		$xform->objparams["actions"][] = array("type" => "db","elements" => array("action","db",$table,"id=$user_id"),);
		$xform->setObjectparams("main_id","$user_id");
		$xform->setObjectparams("main_where","id=$user_id");
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






//------------------------------> User lšschen
if($func == "delete"){
	$query = "delete from $table where id='".$user_id."' ";
	$delsql = new rex_sql;
	$delsql->debugsql=0;
	$delsql->setQuery($query);
	$func = "";
	echo rex_info("User wurde gel&ouml;scht");
}











//------------------------------> Userliste
if($func == ""){

	/** Suche  **/
	$addsql = "";
	$link	= "";
	
	$csuchtxt = rex_request("csuchtxt","string","");
	if($csuchtxt != ""){
		$link .= "&csuchtxt=".urlencode($csuchtxt);
	}
	
	$csuchfeld = rex_request("csuchfeld","array");
	$SUCHSEL = new rex_select();
	$SUCHSEL->setMultiple(1); 
	$SUCHSEL->setSize(5); 
	$SUCHSEL->setName("csuchfeld[]");
	$SUCHSEL->setStyle("width:100%;");

	$ssql 	= new rex_sql();
	//$ssql->debugsql = 1;
	$ssql->setQuery("select * from ".$table_field." order by prior");

	for($i=0;$i<$ssql->getRows(); $i++){
		$SUCHSEL->addOption($ssql->getValue("name"),$ssql->getValue("userfield"));
		if(!is_array($csuchfeld))
			$SUCHSEL->setSelected($ssql->getValue("field"));
		$ssql->next();
	}
	foreach($csuchfeld as $cs){
		$SUCHSEL->setSelected($cs);
		$link .= "&csuchfeld[]=".($cs);
	}	
	

	$cstatus = rex_request("cstatus","string");
	$STATUSSEL = new rex_select();
	$STATUSSEL->setName("cstatus");
	$STATUSSEL->setStyle("width:100%;");
	$STATUSSEL->addOption("Aktiv & Inaktiv", "");
	$STATUSSEL->addOption("Aktiv", 1);
	$STATUSSEL->addOption("Inaktiv", 0);	
	if($cstatus != ""){
		$STATUSSEL->setSelected($cstatus);
		$link .= "&cstatus=".urlencode($cstatus);
	}

	$suchform = '<table width=770 cellpadding=5 cellspacing=1 border=0 bgcolor=#ffffff class="rex-table">';
	$suchform .= '<form action="'.$_SERVER['PHP_SELF'].'" method="poost" >';
	$suchform .= '<input type="hidden" name="page" value="'.$page.'" />';
	$suchform .= '<input type="hidden" name="subpage" value="'.$subpage.'" />';
	$suchform .= '<input type="hidden" name="csuche" value="1" />';
	$suchform .= '<tr>
		<th>Suchbegriff</th>
		<th>Tabellenfelder über die gesucht wird</th>
		<th>Status der gesuchten Einträge</th><th>&nbsp;</th>
		</tr>';	
	$suchform .= '<tr>
		<td class="grey" valign="top"><input type="text" name="csuchtxt" value="'.htmlspecialchars(stripslashes($csuchtxt)).'" style="width:100%;" /></td>
		<td class="grey" valign="top">'.$SUCHSEL->get().'</td><td class="grey" valign="top">'.$STATUSSEL->get().'</td>
		<td class="grey" valign="top"><input type="submit" name="send" value="suchen"  class="inp100" /></td>
		</tr>';
	$suchform .= '</form>';
	$suchform .= '</table><br />';
	
	echo $suchform;
	
	$csuche = rex_request("csuche","int","0");
	
	
	
	if($csuche == 1)
	{
		if(is_array($csuchfeld) && count($csuchfeld)>0 && $csuchtxt != ""){
			$addsql .= "WHERE (";
			foreach($csuchfeld as $cs){
				$addsql .= " `".$cs."` LIKE  '%".$csuchtxt."%' OR ";			
			}
			$addsql = substr($addsql, 0, strlen($addsql)-3 );
			$addsql .= ")";
		}	
		$link .= "&csuche]".$csuche;
		
	}
	if($cstatus != ""){
		if($addsql == ""){ $addsql .= " WHERE "; } else { $addsql .= " AND "; }
		$addsql .= " `status`='".$cstatus."' ";
	}
	
	echo "<table cellpadding=5 class=rex-table><tr><td><a href=index.php?page=".$page."&subpage=".$subpage."&func=add><b>+ $bezeichner anlegen</b></a></td></tr></table><br />";
	
	$sql = "select * from $table $addsql";

	$list = rex_list::factory($sql,30);
	$list->setColumnFormat('id', 'Id');

	$list->setColumnParams("id", array("user_id"=>"###id###","func"=>"edit"));
	$list->setColumnParams("login", array("user_id"=>"###id###","func"=>"edit"));
	$list->setColumnParams("email", array("user_id"=>"###id###","func"=>"edit"));

	$list->addParam("page", $page);
	$list->addParam("subpage", $subpage);
	$list->addParam("csuchtxt", $csuchtxt);
	$list->addParam("cstatus", $cstatus );
	$list->addParam("csuche", $csuche );
	foreach($csuchfeld as $cs)
	{
		$list->addParam("csuchfeld[]", $cs);
	}

	$guf = new rex_sql;
	$guf->setQuery("select * from ".$table_field." where inlist<>1 order by prior");
	$gufa = $guf->getArray();
	foreach($gufa as $key => $value)
	{
		$list->removeColumn($value["userfield"]);
	}

	$list->addColumn('l&ouml;schen','l&ouml;schen');
	$list->setColumnParams("l&ouml;schen", array("user_id"=>"###id###","func"=>"delete"));
	
	/*
	$list->setColumnSortable('name');
	$list->addColumn('testhead','###id### - ###name###',-1);
	$list->addColumn('testhead2','testbody2');
	$list->setCaption('thomas macht das css');
	*/
	
	echo $list->get();

}