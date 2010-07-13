<?php

// ********************************************* TABLE ADD/EDIT/LIST

$table = $REX['TABLE_PREFIX'].'em_table';
$table_field = $REX['TABLE_PREFIX'].'em_field';

$bezeichner = "Tabelle";

$func = rex_request("func","string","");
$page = rex_request("page","string","");
$subpage = rex_request("subpage","string","");
$table_id = rex_request("table_id","int");

$show_list = TRUE;

if($func == "update")
{
	rex_em_generateAll();
	echo rex_info("Tabelle und Felder wurden erstellt und/oder aktualisiert");
	$func = "";
}

// ********************************************* FORMULAR
if($func == "add" || $func == "edit")
{
	
	$xform = new rex_xform;
  // $xform->setDebug(TRUE);
	$xform->setHiddenField("page",$page);
	$xform->setHiddenField("subpage",$subpage);
	$xform->setHiddenField("func",$func);
	$xform->setActionField("showtext",array("","Vielen Dank fuer die Eintragung"));
	$xform->setObjectparams("main_table",$table); // für db speicherungen und unique abfragen

  $xform->setValueField("text",array("prio","Priorit&auml;t"));
	
  if($func == "edit")
	{
    $xform->setValueField("showvalue",array("name","Tabellenname"));
		$xform->setHiddenField("table_id",$table_id);
		$xform->setActionField("db",array($table,"id=$table_id"));
		$xform->setObjectparams("main_id",$table_id);
		$xform->setObjectparams("main_where","id=$table_id");
		$xform->setGetdata(true); // Datein vorher auslesen
	}elseif($func == "add")
	{
	    $xform->setValueField("text",array("name","Tabellenname"));
	    $xform->setValidateField("notEmpty",array("name","Bitte tragen Sie den Tabellenname ein"));
	    $xform->setValidateField("preg_match",array("name","/([a-z\_])*/","Bitte tragen Sie beim Tabellenname nur Buchstaben ein"));
	    $xform->setValidateField("customfunction",array("name","rex_em_checkLabelInTable","","Dieser Tabellenname ist bereits vorhanden"));
		$xform->setActionField("db",array($table));
	}
	
  $xform->setValueField("text",array("label","Label"));
	$xform->setValueField("textarea",array("description","Beschreibung"));
	$xform->setValueField("checkbox",array("status","Aktiv"));
  // $xform->setValueField("fieldset",array("fs-list","Liste"));
  $xform->setValueField("text",array("list_amount","Datens&auml;tze pro Seite"));
  $xform->setValueField("checkbox",array("search","Suche aktiv"));
  $xform->setValidateField("type",array("list_amount","int","Bitte geben Sie eine Zahl f&uuml;r die Datens&auml;tze pro Seite ein"));

  $xform->setValueField("checkbox",array("hidden","In Navigation versteckt"));
  $xform->setValueField("checkbox",array("export","Export der Daten erlauben"));
  
  $xform->setValidateField("empty",array("name","Bitte den Namen eingeben"));
  $form = $xform->getForm();
	
  if($xform->objparams["form_show"])
  {	
  	if($func == "edit")
	    echo '<div class="rex-area"><h3 class="rex-hl2">Tabelle editieren</h3><div class="rex-area-content">';
	  else
	    echo '<div class="rex-area"><h3 class="rex-hl2">Tabelle hinzufügen</h3><div class="rex-area-content">';
    echo $form;
    echo '</div></div>';
    echo '<br />&nbsp;<br /><table cellpadding="5" class="rex-table"><tr><td><a href="index.php?page='.$page.'&amp;subpage='.$subpage.'"><b>&laquo; '.$I18N->msg('em_back_to_overview').'</b></a></td></tr></table>';
    $show_list = FALSE;
  }else
  {
    if($func == "edit")
      echo rex_info("Vielen Dank f&uuml;r die Aktualisierung.");
    elseif($func == "add")
      echo rex_info("Vielen Dank f&uuml;r den Eintrag.");
  }
	
}





// ********************************************* LOESCHEN
if($func == "delete"){

	// TODO:
	// querloeschen - bei be_em_relation, muss die zieltabelle auch bearbeitet werden + die relationentabelle auch geloescht werden

	$query = "delete from $table where id='".$table_id."' ";
	$delsql = new rex_sql;
	// $delsql->debugsql=1;
	$delsql->setQuery($query);
	$query = "delete from $table_field where table_id='".$table_id."' ";
	$delsql->setQuery($query);
	
	$func = "";
	echo rex_info($bezeichner." wurde gel&ouml;scht");
}





// ********************************************* LISTE
if($show_list){
  
  // formatting func fuer status col
	function rex_em_status_col($params)
	{
    global $I18N;
    $list = $params["list"];
    
    return $list->getValue("status") == 1 ?
      $I18N->msg("em_tbl_active") :
      $I18N->msg("em_tbl_inactive"); 
	}
	
  
	echo "<table cellpadding=5 class=rex-table><tr><td>
		<a href=index.php?page=".$page."&subpage=".$subpage."&func=add><b>+ $bezeichner anlegen</b></a>
		 | 
		<a href=index.php?page=".$page."&subpage=".$subpage."&func=update><b>Tabellen und Felder updaten</b></a>
		
		</td></tr></table><br />";
	
	$sql = "select * from $table order by prio,name";

	$list = rex_list::factory($sql,30);

	// $list->setColumnParams("id", array("table_id"=>"###id###","func"=>"edit"));
	$list->removeColumn("id");
	$list->removeColumn("list_amount");
	$list->removeColumn("search");
  $list->removeColumn("hidden");
  $list->removeColumn("export");
  // $list->removeColumn("label");
  // $list->removeColumn("prio");
	
  $list->setColumnFormat('status', 'custom', 'rex_em_status_col');
	$list->setColumnParams("name", array("table_id"=>"###id###","func"=>"edit"));
	
	$list->addColumn($I18N->msg("em_importcsv"),$I18N->msg("em_importcsv"));
	$list->setColumnParams($I18N->msg("em_importcsv"), array("subpage"=>"import","table_name"=>"###name###"));

	$list->addColumn($I18N->msg("em_edit"),$I18N->msg("em_editfield"));
	$list->setColumnParams($I18N->msg("em_edit"), array("subpage"=>"field","table_name"=>"###name###"));

	$list->addColumn($I18N->msg("em_delete"),$I18N->msg("em_delete"));
	$list->setColumnParams($I18N->msg("em_delete"), array("table_id"=>"###id###","func"=>"delete"));

	echo $list->get();
}