<?php

class rex_xform_mysql_datetime extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		$this->value = date("Y-m-d H-i-s");
		$email_elements[$this->elements[1]] = $this->value;
		if (!isset($this->elements[3]) || $this->elements[3] != "no_db") 
		  $sql_elements[$this->elements[1]] = $this->value;
	}
	
	function getDescription()
	{
		return "mysql_datetime -> Beispiel: mysql_datetime|status|[no_db]";
	}
}

?>