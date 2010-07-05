<?php

// ********************************************* FIELD ADD/EDIT/LIST

$table = $REX['TABLE_PREFIX'].'em_field';

$bezeichner = $I18N->msg("em_tablefield");

$func = rex_request("func","string","list");
$page = rex_request("page","string","");
$subpage = rex_request("subpage","string","");
$table_name = rex_request("table_name","string");
$type_id = rex_request("type_id","string");
$type_name = rex_request("type_name","string");
$field_id = rex_request("field_id","int");
$show_list = TRUE;


$TYPE = array('value' => $I18N->msg("em_values"), 'validate' => $I18N->msg("em_validates"), 'action' => $I18N->msg("em_actios"));

$tb = new rex_sql();
// $tb->debugsql = 1;
$tb->setQuery('select * from '.$REX['TABLE_PREFIX'].'em_table where name="'.$table_name.'"');
if($tb->getRows()==0)
{
	echo rex_warning($I18N->msg("em_tablenotexists"));
	echo '<br />
	 <table cellpadding="5" class="rex-table">
	 <tr>
	   <td><a href="index.php?page='.$page.'&amp;subpage="><b>&laquo; '.$I18N->msg('em_back_to_overview').'</b></a></td>
	 </tr>
	 </table>';
	$func = "nothing";

}else
{
	$table_name = $tb->getValue("name");
	echo '<br /><table cellpadding="5" class="rex-table"><tr><td>'.$I18N->msg("em_table").': <b>'.$tb->getValue("label").'</b> - '.$tb->getValue("description").'</td></tr></table><br />';
}





// ********************************************* CHOOSE FIELD
$types = rex_xform::getTypeArray();
if($func == "choosenadd")
{
	// type and choose !!

	$link = 'index.php?page=editme&subpage=field&table_name='.$table_name.'&func=add&';
	?>

<div class="rex-addon-output">
<h2 class="rex-hl2"><?php echo $I18N->msg('em_choosenadd'); ?></h2>

<div class="rex-addon-content">
<p class="rex-tx1"><?php echo $I18N->msg('em_choosenadd_description'); ?></p>
</div>
</div>

<div class="rex-addon-output">
<div class="rex-area-col-2">
<div class="rex-area-col-a">
<h3 class="rex-hl2"><?php echo $TYPE['value']; ?></h3>
<div class="rex-area-content">
<p class="rex-tx1"><?php

if(isset($types['value']))
foreach($types['value'] as $k => $v)
{
	echo '<p class="rex-button"><a class="rex-button" href="'.$link.'type_id=value&type_name='.$k.'">'.$k.'</a> '.$v['description'].'</p>';
}

?></p>
</div>
</div>
<div class="rex-area-col-b">
<h3 class="rex-hl2"><?php echo $TYPE['validate']; ?></h3>
<div class="rex-area-content">
<p class="rex-tx1"><?php
if(isset($types['validate']))
foreach($types['validate'] as $k => $v)
{
	echo '<p class="rex-button"><a class="rex-button" href="'.$link.'type_id=validate&type_name='.$k.'">'.$k.'</a> '.$v['description'].'</p>';
}

?></p>
</div>
</div>
</div>
</div>

<div class="rex-addon-output">
<h2 class="rex-hl2"><?php echo $TYPE['action']; ?></h2>
<div class="rex-addon-content">
<p class="rex-tx1"><?php
if(isset($types['action']))
foreach($types['action'] as $k => $v)
{
	echo '<p class="rex-button">"<a href="'.$link.'type_id=action&type_name='.$k.'">'.$k.'</a>" - '.$v['description'].'</p>';
}

?></p>
</div>
</div>

<?php

}





// ********************************************* FORMULAR
if( ($func == "add" || $func == "edit" )  && isset($types[$type_id][$type_name]) )
{

	$xform = new rex_xform;
	// $xform->setDebug(TRUE);

	$xform->setHiddenField("page", $page);
	$xform->setHiddenField("subpage", $subpage);
	$xform->setHiddenField("func", $func);

	$xform->setValueField("hidden", array("table_name",$table_name,"REQUEST"));
	$xform->setValueField("hidden", array("type_name",$type_name,"REQUEST"));
	$xform->setValueField("hidden", array("type_id",$type_id,"REQUEST"));

	$xform->setValueField("text", array("prio","Prioritaet"));

	$i = 0;
	foreach($types[$type_id][$type_name]['values'] as $v)
	{
		$i++;

		switch($v['type'])
		{

			case("name"):

				if($func == "edit" )
				{
					$xform->setValueField("showvalue",array("f".$i,"Name"));
				}else
				{
					if(!isset($v["value"]))
					$v["value"] = "";
					$xform->setValueField("text",array("f".$i,"Name",$v["value"]));
					$xform->setValidateField("notEmpty",array("f".$i,$I18N->msg("em_validatenamenotempty")));
					$xform->setValidateField("preg_match",array("f".$i,"/(([a-z])+([a-z0-9\_])*)/",$I18N->msg("em_validatenamepregmatch")));
					$xform->setValidateField("customfunction",array("f".$i,"rex_em_checkField",$table_name,$I18N->msg("em_validatenamecheck")));
				}
				break;

			case("no_db"):
				$xform->setValueField("checkbox",array("f".$i,$I18N->msg("em_donotsaveindb"),1,0));
				break;

			case("boolean"):
				// checkbox|check_design|Bezeichnung|Value|1/0|[no_db]
				$xform->setValueField("checkbox",array("f".$i,$v['label']));
				break;

			case("select"):
				// select|gender|Geschlecht *|Frau=w;Herr=m|[no_db]|defaultwert|multiple=1
				$xform->setValueField("select",array("f".$i,$v['label'],$v['definition'],"",$v['default'],0));
				break;

			case("table"):
				// ist fest eingetragen, damit keine Dinge durcheinandergehen
				
				if($func == "edit" )
				{
					$xform->setValueField("showvalue",array("f".$i,$v['label']));
				}else
				{
          $tables = rex_em_getTables();
					$v['definition'] = "";
          foreach($tables as $t)
					{
						if($v['definition'] !="") $v['definition'] .= ';';
					  $v['definition'] .= $t["name"]."=".$t["name"];
					}
					
					$xform->setValueField("select",array("f".$i,$v['label'],$v['definition'],"","",0));
					// $xform->setValueField("text",array("f".$i,$v['label']));
				}
				break;

				// Todo:
			case("table.field"):
				// Todo:
			case("getName"):
				// Todo:
			case("getNamess"):
				// Todo:
			default:
				$xform->setValueField("text",array("f".$i,$v['label']));
		}

	}
	
	$xform->setActionField("showtext",array("",'<p>'.$I18N->msg("em_thankyouforentry").'</p>'));
	$xform->setObjectparams("main_table",$table); // für db speicherungen und unique abfragen

	if($func == "edit")
	{
		$xform->setHiddenField("field_id",$field_id);
		$xform->setActionField("be_em_db",array($table,"id=$field_id"));
		$xform->setObjectparams("main_id",$field_id);
		$xform->setObjectparams("main_where","id=$field_id");
		$xform->setGetdata(true); // Datein vorher auslesen
	}elseif($func == "add")
	{
		$xform->setActionField("be_em_db",array($table));
	}

	if($type_id == "value")
	{
		$xform->setValueField("checkbox",array("list_hidden",$I18N->msg("em_hideinlist"),1,"0"));
    $xform->setValueField("checkbox",array("search",$I18N->msg("em_useassearchfieldalidatenamenotempty")));
	}else	if($type_id == "validate")
	{
		$xform->setValueField("hidden",array("list_hidden",1));
	}

	$form = $xform->getForm();

	if($xform->objparams["form_show"])
	{
		if($func == "add")
		echo '<div class="rex-area"><h3 class="rex-hl2">'.$I18N->msg("em_addfield").' "'. $type_name .'"</h3><div class="rex-area-content">';
		else
		echo '<div class="rex-area"><h3 class="rex-hl2">'.$I18N->msg("em_editfield").' "'. $type_name .'"</h3><div class="rex-area-content">';
		echo $form;
		echo '</div></div>';
		echo '<br />&nbsp;<br /><table cellpadding="5" class="rex-table"><tr><td><a href="index.php?page='.$page.'&amp;subpage='.$subpage.'&amp;table_name='.$table_name.'"><b>&laquo; '.$I18N->msg('em_back_to_overview').'</b></a></td></tr></table>';
		$func = "";
	}else
	{
		if($func == "edit")
		echo rex_info($I18N->msg("em_thankyouforupdate"));
		elseif($func == "add")
		echo rex_info($I18N->msg("em_thankyouforentry"));
		$func = "list";
	}
}





// ********************************************* LOESCHEN
if($func == "delete"){

	$delsql = new rex_sql;
  // $delsql->debugsql=1;

	$sf = new rex_sql();
	// $sf->debugsql = 1;
	$sf->setQuery('select * from '.$table.' where table_name="'.$table_name.'" and id='.$field_id);
	$sfa = $sf->getArray();
	if(count($sfa) == 1)
	{
		$query = 'delete from '.$table.' where table_name="'.$table_name.'" and id='.$field_id;
		$delsql->setQuery($query);

		echo rex_info($I18N->msg("em_tablefielddeleted"));
	}else
	{
		echo rex_warning($I18N->msg("em_tablefieldnotfound"));
	}

	$func = "list";
}





// ********************************************* LIST
if($func == "list"){

	echo '<table cellpadding=5 class=rex-table><tr><td><a href=index.php?page='.$page.'&subpage='.$subpage.'&table_name='.$table_name.'&func=choosenadd><b>+ '.$I18N->msg("em_addtablefield").'</b></a></td></tr></table><br />';

	$sql = 'select * from '.$table.' where table_name="'.$table_name.'" order by prio';
	$list = rex_list::factory($sql,30);
	$list->setColumnFormat('id', 'Id');

	$list->addParam("page", $page);
	$list->addParam("subpage", $subpage);
	$list->addParam("table_name", $table_name);

	$list->removeColumn('table_name');
	$list->removeColumn('id');

	for($i=3;$i<10;$i++)
	{
		$list->removeColumn('f'.$i);
	}

	$list->addColumn($I18N->msg("em_edit"),$I18N->msg("em_editfield"));
	$list->setColumnParams("editieren", array("field_id"=>"###id###","func"=>"edit",'type_name'=>'###type_name###','type_id'=>'###type_id###',));

	$list->addColumn($I18N->msg("em_delete"),$I18N->msg("em_delete"));
	$list->setColumnParams($I18N->msg("em_delete"), array("field_id"=>"###id###","func"=>"delete"));

	echo $list->get();

}

