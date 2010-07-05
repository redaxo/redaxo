<?php

class rex_xform_validate_existintable extends rex_xform_validate_abstract 
{

	function enterObject(&$warning, $send, &$warning_messages)
	{
		if($send=="1")
		{
			foreach($this->obj_array as $Object)
			{
				$sql = 'select '.$this->elements[2].' from '.$this->elements[3].' WHERE '.$this->elements[4].'="'.$Object->getValue().'" LIMIT 2';
				$cd = rex_sql::factory();
				// $cd->debugsql = 1;
				$cd->setQuery($sql);
				if ($cd->getRows()!=1)
				{
					$warning["el_" . $Object->getId()] = $this->params["error_class"];
					$warning_messages[] = $this->elements[5];
				}
			}
		}
	}
	
	function getDescription()
	{
		return "existintable -> prüft ob vorhanden, beispiel: validate|existintable|label|tablename|feldname|warning_message";
	}
}

?>