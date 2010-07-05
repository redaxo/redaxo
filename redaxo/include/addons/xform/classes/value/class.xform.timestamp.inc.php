<?php

class rex_xform_timestamp extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		$this->value = time();
		$email_elements[$this->elements[1]] = $this->value;
		if (!isset($this->elements[2]) || $this->elements[2] != "no_db") $sql_elements[$this->elements[1]] = $this->value;
	}
	
	function getDescription()
	{
		return "timestamp -> Beispiel: timestamp|status|[no_db]";
	}
}

?>