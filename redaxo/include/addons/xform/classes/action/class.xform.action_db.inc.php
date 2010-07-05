<?php

/*
$objparams["actions"][] = "db"; // z.b. email, datenbank, als datei speichern etc.
$objparams["action_params"][] = array(
	"table" => "REX_VALUE[8]",
	"where" => "REX_VALUE[8]",
	);
*/


class rex_xform_action_db extends rex_xform_action_abstract
{
	
	function execute()
	{
		// echo "DB EXECUTE";
		// return;

		$sql = rex_sql::factory();
		if ($this->params["debug"]) $sql->debugsql = TRUE;
    
    	$main_table = "";
    	if (isset($this->action["elements"][2]) && $this->action["elements"][2] != "") $main_table = $this->action["elements"][2];
    	else $main_table = $this->params["main_table"];
    	
    	if ($main_table == "")
    	{
    		$this->params["form_show"] = TRUE;
			$this->params["hasWarnings"] = TRUE;
			$this->params["warning_messages"][] = $this->params["Error-Code-InsertQueryError"];
			return FALSE;
    	}
    	
    
    	$sql->setTable($main_table);

      	$where = "";
		if (isset($this->action["elements"][3]) && trim($this->action["elements"][3]) != "") $where = trim($this->action["elements"][3]);

		// SQL Objekt mit Werten füllen
		foreach($this->elements_sql as $key => $value)
		{
			$sql->setValue($key, $value);
			if ($where != "") $where = str_replace('###'.$key.'###',addslashes($value),$where);
		}
			
		if ($where != "")
		{
			$sql->setWhere($where);
			$sql->update();
			$flag = "update";
		}else
		{
			$sql->insert();
			$flag = "insert";
			$id = $sql->getLastId();
			
			$this->params["main_id"] = $id;
			$this->elements_email["ID"] = $id;
			// $this->elements_sql["ID"] = $id;
			if ($id == 0)
			{
				$this->params["form_show"] = TRUE;
				$this->params["hasWarnings"] = TRUE;
				$this->params["warning_messages"][] = $this->params["Error-Code-InsertQueryError"];
			}
		}
	}

	function getDescription()
	{
		return "action|db|tblname|[where]";
	}

}

?>