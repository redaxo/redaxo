<?php

/*
$objparams["actions"][] = "db"; // z.b. email, datenbank, als datei speichern etc.
$objparams["action_params"][] = array(
	"table" => "REX_VALUE[8]",
	"where" => "REX_VALUE[8]",
	);
*/


class rex_xform_action_db_query extends rex_xform_action_abstract
{
	
	function execute()
	{

		$query = trim($this->action["elements"][2]);

		if($query == "")
		{
			if ($this->params["debug"])
			{
				echo 'ActionQuery Error: no query';
			}
			return;
		}

		$sql = rex_sql::factory();
		if ($this->params["debug"]) $sql->debugsql = TRUE;

		// SQL Objekt mit Werten füllen
		foreach($this->elements_sql as $key => $value)
		{
			$query = str_replace('###'.$key.'###',addslashes($value),$query);
		}

		$sql->setQuery($query);

		if( $sql->getError() != "")
		{
			$this->params["form_show"] = TRUE;
			$this->params["hasWarnings"] = TRUE;
			$this->params["warning_messages"][] = $this->action["elements"][3];
		}

	}

	function getDescription()
	{
		return "action|db_query|query|Fehlermeldung";
	}

}

?>