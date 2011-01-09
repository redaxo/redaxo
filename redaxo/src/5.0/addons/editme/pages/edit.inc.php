<?php

// ********************************************* DATA ADD/EDIT/LIST

$func = rex_request("func","string","");
$data_id = rex_request("data_id","int","");
$rex_em_opener_field = rex_request("rex_em_opener_field","int",-1);
$rex_em_opener_fieldname = rex_request("rex_em_opener_fieldname","string","");
$rex_em_opener_info = rex_request("rex_em_opener_info","string","");
$rex_em_filter = rex_request("rex_em_filter","array");
$rex_em_set = rex_request("rex_em_set","array");
$rex_em_searchfields = rex_request("rex_em_searchfields","array");
$rex_em_searchtext = rex_request("rex_em_searchtext","string");
$rex_em_search = rex_request("rex_em_search","int","0");

// ********************************** DFAULRT - LISTE AUSGEBEN
$show_list = TRUE;

// ********************************** TABELLE HOLEN
foreach($tables as $table)
{
	$name = $table['name'];
	$id = $table['id'];
	$table["tablename"] = rex_em_getTableName($table['name']);
	if($subpage == $table['name'])
	{
		echo '<table cellpadding="5" class="rex-table"><tr><td><b>'.$table["label"].'</b> - '.$table["description"];
		if($rex_em_opener_info != "")
		{
			echo ' - '.$I18N->msg("em_openerinfo").': '.$rex_em_opener_info;
		}
		echo '</td></tr></table><br />';
		break; // Wenn Tabelle gefunden - abbrechen
	}
}

// ********************************** FELDER HOLEN
$fields = rex_em_getFields($table['name']);
$field_names = array();
foreach($fields as $field){ if($field["type_id"] == "value") { $field_names[] = $field["f1"]; } }

// ********************************** FILTER UND SETS PRFEN
$em_url_filter = "";
if(count($rex_em_filter)>0) {
	foreach($rex_em_filter as $k => $v) {
		if(in_array($k,$field_names)) { $em_url_filter .= '&amp;rex_em_filter['.$k.']='.urlencode($v); }
		else { unset($rex_em_filter[$k]); }
	}
};
$em_url_set = "";
if(count($rex_em_set)>0) {
	foreach($rex_em_set as $k => $v) {
		if(in_array($k,$field_names)) { $em_url_set .= '&amp;rex_em_set['.$k.']='.urlencode($v); }
		else { unset($rex_em_set[$k]); }
	}
};
$em_url = $em_url_filter.$em_url_set;

$em_rex_list = "";
$em_rex_list .= '&amp;list='.urlencode(rex_request('list','string'));
$em_rex_list .= '&amp;sort='.urlencode(rex_request('sort','string'));
$em_rex_list .= '&amp;sorttype='.urlencode(rex_request('sorttype','string'));
$em_rex_list .= '&amp;start='.urlencode(rex_request('start','string'));




// ---------- Opener Field .. dann wird rahmen weggeCSSt..
if($rex_em_opener_field > -1)
{
	echo '<link rel="stylesheet" type="text/css" href="../files/addons/editme/popup.css" media="screen, projection, print" />';
}







// ********************************************* LOESCHEN
if($func == "delete")
{
	$query = 'delete from '.$table["tablename"].' where id='.$data_id;
	$delsql = new rex_sql;
	// $delsql->debugsql=1;
	$delsql->setQuery($query);
	$func = "";
	echo rex_info($I18N->msg("em_datadeleted"));
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

	// Speichern der Felder fr Popup und spezifische Zuweisungen
	$xform->setHiddenField("rex_em_opener_field",$rex_em_opener_field);
	$xform->setHiddenField("rex_em_opener_fieldname",$rex_em_opener_fieldname);
	if(count($rex_em_filter)>0) { foreach($rex_em_filter as $k => $v) { $xform->setHiddenField('rex_em_filter['.$k.']',$v); } };
	if(count($rex_em_set)>0) { foreach($rex_em_set as $k => $v) { $xform->setHiddenField('rex_em_set['.$k.']',$v); } };

	if(count($rex_em_searchfields)>0) { foreach($rex_em_searchfields as $k => $v) { $xform->setHiddenField('rex_em_searchfields['.$k.']',$v); } };
	$xform->setHiddenField("rex_em_search",$rex_em_search);
	$xform->setHiddenField("rex_em_searchtext",$rex_em_searchtext);

	// Speichern der Felder fr die rex_list
	$xform->setHiddenField('list',rex_request('list','string'));
	$xform->setHiddenField('sort',rex_request('sort','string'));
	$xform->setHiddenField('sorttype',rex_request('sorttype','string'));
	$xform->setHiddenField('start',rex_request('start','string'));

	foreach($fields as $field)
	{
		$type_name = $field["type_name"];
		$type_id = $field["type_id"];
		$values = array();
		for($i=1;$i<10;$i++){ $values[] = $field["f".$i]; }
		if($type_id == "value")
		{
			$xform->setValueField($field["type_name"],$values);
		}elseif($type_id == "validate")
		{
			$xform->setValidateField($field["type_name"],$values);
		}elseif($type_id == "action")
		{
			$xform->setActionField($field["type_name"],$values);
		}
	}

	// $xform->setValueField('be_em_submit',array('em_submit','0,1','speichern und schliessen, speichern','0','no_db'));

	// $xform->setActionField("showtext",array("","Vielen Dank fr die Eintragung"));
	$xform->setObjectparams("main_table",$table["tablename"]); // für db speicherungen und unique abfragen

	$xform->setObjectparams("submit_btn_label",$I18N->msg('em_submit'));
	
	if($func == "edit")
	{
		$xform->setHiddenField("data_id",$data_id);
		$xform->setActionField("db",array($table["tablename"],"id=$data_id"));
		$xform->setObjectparams("main_id",$data_id);
		$xform->setObjectparams("main_where","id=$data_id");
		$xform->setGetdata(true); // Datein vorher auslesen
	}elseif($func == "add")
	{
		$xform->setActionField("db",array($table["tablename"]));
	}

	$xform->setObjectparams("rex_em_set",$rex_em_set);

	$form = $xform->getForm();

	// Formular ausgeben wenn
	// - fehler
	// - edit und nur save
	// - add und nur save

	// formular nicht ausgeben wenn
	// - wenn edit und schliessen
	// - add und schliessen
	// - und nur wenn kein fehler

	//

	if($xform->objparams["form_show"] || ($xform->objparams["form_showformafterupdate"] ))
	{

		if($xform->objparams["send"])
		{
			if($func == "edit")
			{
				echo rex_info($I18N->msg("em_thankyouforupdate"));
			}elseif($func == "add"){
				echo rex_info($I18N->msg("em_thankyouforentry"));
			}
		}

		if($func == "edit")
		{
			echo '<div class="rex-area"><h3 class="rex-hl2">'.$I18N->msg("em_editdata").'</h3><div class="rex-area-content">';
		}else
		{
			echo '<div class="rex-area"><h3 class="rex-hl2">'.$I18N->msg("em_adddata").'</h3><div class="rex-area-content">';
		}
		echo $form;
		echo '</div></div>';
		echo '<br />&nbsp;<br /><table cellpadding="5" class="rex-table"><tr><td><a href="index.php?page='.$page.'&amp;subpage='.$subpage.'&rex_em_opener_field='.$rex_em_opener_field.'&rex_em_opener_fieldname='.htmlspecialchars($rex_em_opener_fieldname).$em_url.$em_rex_list.'"><b>&laquo; '.$I18N->msg('em_back_to_overview').'</b></a></td></tr></table>';
		$show_list = FALSE;
	}else
	{
		if($func == "edit")
		{
			echo rex_info($I18N->msg("em_thankyouforupdate"));
		}elseif($func == "add"){
			echo rex_info($I18N->msg("em_thankyouforentry"));
		}
	}

}





// ********************************************* LIST
if($show_list)
{
	echo '<table cellpadding="5" class="rex-table"><tr><td><a href="index.php?page='.$page.'&subpage='.$subpage.'&func=add&rex_em_opener_field='.$rex_em_opener_field.'&rex_em_opener_fieldname='.htmlspecialchars($rex_em_opener_fieldname).$em_url.$em_rex_list.'"><b>+ '.$I18N->msg("em_add").'</b></a></td><td style="text-align:right;"><a href=""><b>'.$I18N->msg('em_export').'</b></a></td></tr></table><br />';

	// ----- SUCHE
	if($table["search"]==1)
	{
		$list = rex_request("list","string","");
		$start = rex_request("start","string","");
		$sort = rex_request("sort","string","");
		$sorttype = rex_request("sorttype","string","");

		$addsql = "";

		$search_field_select = new rex_select();
		$search_field_select->setMultiple(1);
		$search_field_select->setSize(5);
		$search_field_select->setName("rex_em_searchfields[]");
		$search_field_select->setStyle("width:100%;");

		$fields = rex_em_getFields($table['name']);
		$field_names = array();
		foreach($fields as $field){ if($field["type_id"] == "value" && $field["search"] == 1) { $search_field_select->addOption($field["f2"].' ['.$field["f1"].']',$field["f1"]); } }
		foreach($rex_em_searchfields as $cs) { $search_field_select->setSelected($cs); }

		$suchform = '<table width=770 cellpadding=5 cellspacing=1 border=0 bgcolor=#ffffff class="rex-table">';
		$suchform .= '<form action="'.$_SERVER['PHP_SELF'].'" method="poost" >';
		$suchform .= '<input type="hidden" name="page" value="'.$page.'" />';
		$suchform .= '<input type="hidden" name="subpage" value="'.$subpage.'" />';

		$suchform .= '<input type="hidden" name="rex_em_opener_field" value="'.$rex_em_opener_field.'" />';
		$suchform .= '<input type="hidden" name="rex_em_opener_fieldname" value="'.$rex_em_opener_fieldname.'" />';

		if(count($rex_em_filter)>0) { foreach($rex_em_filter as $k => $v) { $suchform .= '<input type="hidden" name="rex_em_filter['.$k.']" value="'.htmlspecialchars(stripslashes($v)).'" />'; } }
		if(count($rex_em_set)>0) { foreach($rex_em_set as $k => $v) { $suchform .= '<input type="hidden" name="rex_em_set['.$k.']" value="'.htmlspecialchars(stripslashes($v)).'" />'; } }
		if($rex_em_opener_field >-1) { $suchform .= '<input type="hidden" name="rex_em_opener_field" value="'.htmlspecialchars(stripslashes($rex_em_opener_field)).'" />'; };
		if($rex_em_opener_fieldname != "") { $suchform .= '<input type="hidden" name="rex_em_opener_fieldname" value="'.htmlspecialchars(stripslashes($rex_em_opener_fieldname)).'" />'; };
		if($rex_em_opener_info != "") { $suchform .= '<input type="hidden" name="rex_em_opener_info" value="'.htmlspecialchars(stripslashes($rex_em_opener_info)).'" />'; };

		if($list != "") { $suchform .= '<input type="hidden" name="list" value="'.htmlspecialchars(stripslashes($list)).'" />'; };
		if($start != "") { $suchform .= '<input type="hidden" name="start" value="'.htmlspecialchars(stripslashes($start)).'" />'; };
		if($sort != "") { $suchform .= '<input type="hidden" name="sort" value="'.htmlspecialchars(stripslashes($sort)).'" />'; };
		if($sorttype != "") { $suchform .= '<input type="hidden" name="sorttype" value="'.htmlspecialchars(stripslashes($sorttype)).'" />'; };

		$suchform .= '<input type="hidden" name="rex_em_search" value="1" />';
		$suchform .= '<tr>
			<th>'.$I18N->msg('em_searchtext').'</th>
			<th>'.$I18N->msg('em_searchfields').'</th>
			<th>&nbsp;</th>
			</tr>';	
		$suchform .= '<tr>
			<td class="grey" valign="top"><input type="text" name="rex_em_searchtext" value="'.htmlspecialchars(stripslashes($rex_em_searchtext)).'" style="width:100%;" /></td>
			<td class="grey" valign="top">'.$search_field_select->get().'</td>
			<td class="grey" valign="top"><input type="submit" name="send" value="'.$I18N->msg('em_search').'"  class="inp100" /></td>
			</tr>';
		$suchform .= '</form>';
		$suchform .= '</table><br />';

		echo $suchform;


	}

	$where = false;

	// ---------- SQL AUFBAUEN
	$sql = "select * from ".$table["tablename"];
	if(count($rex_em_filter)>0)
	{
		$where = true;
		$sql .= ' where ';
		$sql_filter = '';
		foreach($rex_em_filter as $k => $v)
		{
			if($sql_filter != '')
			{
				$sql_filter .= ' AND ';
			}
			$sql_filter .= '`'.$k.'`="'.$v.'"';
		}
		$sql .= $sql_filter;
		// echo $sql;
	}

	if($rex_em_search == 1)
	{
		if(is_array($rex_em_searchfields) && count($rex_em_searchfields)>0 && $rex_em_searchtext != ""){
			if(!$where)
			$sql .= ' WHERE ';
				
			$sql .= '(';
			foreach($rex_em_searchfields as $cs){
				$sql .= " `".$cs."` LIKE  '%".$rex_em_searchtext."%' OR ";
			}
			$sql = substr($sql, 0, strlen($sql)-3 );
			$sql .= ")";
		}
	}


	// ---------- LISTE AUSGEBEN
	if(!isset($table["list_amount"]) || $table["list_amount"]<1)
	$table["list_amount"] = 30;

	$list = rex_list::factory($sql,$table["list_amount"]);
	$list->setColumnFormat('id', 'Id');

	if(count($rex_em_filter)>0) { foreach($rex_em_filter as $k => $v) { $list->addParam('rex_em_filter['.$k.']',$v); } }
	if(count($rex_em_set)>0) { foreach($rex_em_set as $k => $v) { $list->addParam('rex_em_set['.$k.']',$v); } }
	if($rex_em_opener_field >-1) { $list->addParam("rex_em_opener_field",$rex_em_opener_field); };
	if($rex_em_opener_fieldname != "") { $list->addParam("rex_em_opener_fieldname",$rex_em_opener_fieldname); };
	if($rex_em_opener_info != "") { $list->addParam("rex_em_opener_info",$rex_em_opener_info); };

	if($rex_em_search != "") { $list->addParam("rex_em_search",$rex_em_search); };
	if($rex_em_searchtext != "") { $list->addParam("rex_em_searchtext",$rex_em_searchtext); };
	if(count($rex_em_searchfields)>0) { foreach($rex_em_searchfields as $k => $v) { $list->addParam('rex_em_searchfields['.$k.']',$v); } }

	$list->setColumnParams("id", array("data_id"=>"###id###", "func"=>"edit" ));

	$fields = rex_em_getFields($table['name']);
	foreach($fields as $field)
	{
		if($field["type_id"] == "value")
		{
			if($field["list_hidden"] == 1)
			{
				$list->removeColumn($field["f1"]);
			}else
			{
				$list->setColumnSortable($field["f1"]);
			}
		}
	}

	$list->addColumn($I18N->msg('em_edit'),$I18N->msg('em_edit'));
	$list->setColumnParams($I18N->msg('em_edit'), array("data_id"=>"###id###","func"=>"edit","start"=>rex_request("start","string")));

	$list->addColumn($I18N->msg('em_delete'),"- ".$I18N->msg('em_delete'));
	$list->setColumnParams($I18N->msg('em_delete'), array("data_id"=>"###id###","func"=>"delete"));

	// if($rex_em_opener_field){ $list->addColumn('&uuml;bernehmen','<a href="javascript:em_setData('.$rex_em_opener_field.',###id###,\'###'.$rex_em_opener_fieldname.'###\')">&uuml;bernehmen</a>',-1,"asdasd"); }

	echo $list->get();

}
