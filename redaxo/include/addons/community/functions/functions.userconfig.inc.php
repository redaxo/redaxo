<?php

function rex_com_utcreate($table,$feld,$type,$extra1="",$extra2="",$extra3="")
{
	$err_msg = "<b>$feld</b> fehlte in der Usertabelle und wurde nun angelegt.";
	
	$up = new rex_sql;
	switch($type)
	{
		case("8"):
		case("1"):
			// int anlegen
			$up->setQuery("ALTER TABLE `$table` ADD `$feld` INT(11) NOT NULL;");
			break;
		case("2"):
			// varchar anlegen
			$up->setQuery("ALTER TABLE `$table` ADD `$feld` VARCHAR(255) NOT NULL;");
			break;
		case("3"):
			// text anlegen
			$up->setQuery("ALTER TABLE `$table` ADD `$feld` TEXT NOT NULL;");
			break;
		case("4"):
			// varchar anlegen
			$up->setQuery("ALTER TABLE `$table` ADD `$feld` VARCHAR(255) NOT NULL;");
			break;
		case("5"):
			// varchar anlegen
			$up->setQuery("ALTER TABLE `$table` ADD `$feld` VARCHAR(255) NOT NULL;");
			break;
		case("6"):
			// tinyint(4) anlegen
			$up->setQuery("ALTER TABLE `$table` ADD `$feld` TINYINT NOT NULL;");
			break;
		case("7"):
			// float für positionen anlegen
			$up->setQuery("ALTER TABLE `$table` ADD `$feld` FLOAT(10,7) NOT NULL;");
			break;
    case("8"):
      // varchar anlegen
      $up->setQuery("ALTER TABLE `$table` ADD `$feld` VARCHAR(255) NOT NULL;");
      break;
    case("9"):
      // medialist
      $up->setQuery("ALTER TABLE `$table` ADD `$feld` TEXT NOT NULL;");
      break;
    case("10"):
      // media
      $up->setQuery("ALTER TABLE `$table` ADD `$feld` TEXT NOT NULL;");
      break;
      
		default:
			// fehler - typ nicht vorhanden
			$err_msg = "Typ <b>$type</b> nicht gefunden. Feld <b>$feld</b> konnte nicht angelegt werden.";
			break;
	}
	return $err_msg;
}

function rex_com_s_rexlist(&$list,$value)
{
	$list->setValue($value["name"],$value["userfield"]);

	switch($value["type"])
	{
		case("5"):
			// select
			$extra1 = str_replace("=","|",$value["extra1"]);
			$list->setFormat("replace_value",$value["extra1"]);
			break;
		case("6"):
			// bool
			$list->setFormat("replace_value","0|nein|1|ja");
			break;
	}

	switch($value["name"])
	{
		case("status"):
			$list->setFormat("replace_value",'|<span style="color:#c33;">inaktiv</span>|1|<span style="color:#3c3;">aktiv</span>');
			break;
	}
	
	$list->setValueOrder(1);
	
}

function rex_com_s_rexform(&$form,$value)
{
	switch($value["type"])
	{
		case("5"):
			// select
			$extra1 = str_replace("=","|",$value["extra1"]);
			$form->setValue("singleselect",$value["name"],$value["userfield"],$value["mandatory"],$extra1);
			break;
		case("6"):
			// bool
			$form->setValue("checkbox",$value["name"],$value["userfield"]);
			break;
		default:
			$value["mandatory"] = (int) $value["mandatory"];
			$form->setValue("text",$value["name"],$value["userfield"],$value["mandatory"]);
			break;
	}
}


function rex_com_checkFields($table, $table_user)
{


	// **************** bei jedem Aufruf Felder abgleichen
	
	$err_msg = array();
	
	$guf = new rex_sql;
	$guf->setQuery("select * from ".$table." order by prior");
	$fields = array();
	$gufa = $guf->getArray();
	foreach($gufa as $key => $value)
	{
	  $userfield = $value["userfield"];
	  $fields[$userfield] = $value["type"];
	  $extra1[$userfield] = $value["extra1"];
	  $extra2[$userfield] = $value["extra2"];
	  $extra3[$userfield] = $value["extra3"];
	  $utype[$userfield] = $value["type"];
	  // echo "<br />$key - $userfield - ".$value["type"];
	}
	
	// $UT - Feldtypen drin..
	$gu = new rex_sql;
	$gu->setQuery("SHOW COLUMNS from ".$table_user);
	foreach($gu->getArray() as $key => $value)
	{
		$field = $value["Field"];
		$type = $value["Type"];
		// echo "<br />$key - ".$value["Field"]." - ".$value["Type"]." - ".$value["Extra"];
		if ($field=="id") echo ""; // ID wird ignoriert
		elseif (@$fields[$field] != "") echo ""; // Feld vorhanden - alles ist ok
		else {
			// Feld zuviel - Melden
			$err_msg[] = "In der Usertabelle ist folgendes Feld zuviel: <b>$field | $type</b>. Bitte nachträglich hier anlegen.";
		}
		$ufields[$field] = $type;
	}
	
	foreach($fields as $field => $value)
	{
		if (isset($ufields[$field]) &&  $ufields[$field] != "") echo ""; // Feld vorhanden - alles ist gut
		else
		{
			// Feld fehlt -> anlegen
			$err_msg[] = rex_com_utcreate($table_user,$field,$utype[$field],$extra1[$field],$extra2[$field],$extra3[$field]);
		}
	}

	$message = "";
	
	if (count($err_msg)==0)
	{
		return array("status"=>1,"message"=>"Alle Felder wurden überprüft und es wurden keine Fehler gefunden.");
	}else
	{
		return array("status"=>0,"message"=>$err_msg);
	}


}