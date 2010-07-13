<?php

class rex_xform_action_be_em_db extends rex_xform_action_abstract
{

	function execute()
	{

		// START - Spezialfall "be_em_relation"
		/*
		$be_em_table_field = "";
		if($this->elements_sql["type_name"] == "be_em_relation")
		{
			$be_em_table_field = $this->elements_sql["f1"];
			$this->elements_sql["f1"] = $this->elements_sql["f3"]."_".$this->elements_sql["f1"];
		}
		*/
		// ENDE - Spezialfall

		
		
		
		// ********************************* TABLE A

		// $this->params["debug"]= TRUE;
		$sql = rex_sql::factory();
		if ($this->params["debug"]) $sql->debugsql = TRUE;

		$main_table = "";
		if (isset($this->action["elements"][2]) && $this->action["elements"][2] != "")
		{
			$main_table = $this->action["elements"][2];
		}else{
			$main_table = $this->params["main_table"];
		}

		if ($main_table == "")
		{
			$this->params["form_show"] = TRUE;
			$this->params["hasWarnings"] = TRUE;
			$this->params["warning_messages"][] = $this->params["Error-Code-InsertQueryError"];
			return FALSE;
		}

		$sql->setTable($main_table);

		$where = "";
		if (isset($this->action["elements"][3]) && trim($this->action["elements"][3]) != "")
		{
			$where = trim($this->action["elements"][3]);
		}

		// SQL Objekt mit Werten füllen
		foreach($this->elements_sql as $key => $value)
		{
			$sql->setValue($key, $value);
			if ($where != "")
			{
				$where = str_replace('###'.$key.'###',addslashes($value),$where);
			}
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

			$this->elements_email["ID"] = $id;
			// $this->elements_sql["ID"] = $id;
			if ($id == 0)
			{
				$this->params["form_show"] = TRUE;
				$this->params["hasWarnings"] = TRUE;
				$this->params["warning_messages"][] = $this->params["Error-Code-InsertQueryError"];
			}
		}

		return;
		
	}

	function getDescription()
	{
		return "action|be_em_db|tblname|[where]";
	}

}

?>