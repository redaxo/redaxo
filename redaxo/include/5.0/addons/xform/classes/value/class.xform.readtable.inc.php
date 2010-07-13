<?php

class rex_xform_readtable extends rex_xform_abstract
{
	
	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		foreach($email_elements as $k => $v)
		{
			if ($this->elements[3] == $k) $value = $v;
		}
		$gd = rex_sql::factory();
		$gd->setQuery('select * from '.$this->elements[1].' where '.$this->elements[2].'="'.addslashes($v).'"');

		if ($gd->getRows()==1)
		{
			$ar = $gd->get_array();
			foreach($ar[0] as $k => $v)
			{
				$email_elements[$k] = $v;
			}
		}	
		return;
	}

	function getDescription()
	{
		return "readtable|tablename|feldname|label";
	}

}

?>