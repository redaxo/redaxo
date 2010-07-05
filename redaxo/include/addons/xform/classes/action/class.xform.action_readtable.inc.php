<?php

class rex_xform_action_readtable extends rex_xform_action_abstract
{
	
	function execute()
	{

		foreach($this->elements_email as $k => $v)
		{
			if ($this->action["elements"][4] == $k) $value = $v;
		}

		$gd = rex_sql::factory();
		// $gd->debugsql = 1;
		$gd->setQuery('select * from '.$this->action["elements"][2].' where '.$this->action["elements"][3].'="'.addslashes($value).'"');

		if ($gd->getRows()==1)
		{
			$ar = $gd->getArray();
			
			foreach($ar[0] as $k => $v)
			{
				$this->elements_email[$k] = $v;
			}
		}	

		// $email_elements[$this->elements[1]] = stripslashes($this->value);
		// if ($this->elements[4] != "no_db") $sql_elements[$this->elements[1]] = $this->value;
	
		return;


	}

	function getDescription()
	{
		return "action|readtable|tablename|feldname|label";
	}

}

?>