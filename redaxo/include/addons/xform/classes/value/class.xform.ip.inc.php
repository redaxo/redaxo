<?php

class rex_xform_ip extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{	
		$sk = "REMOTE_ADDR";
		if (isset($this->elements[3]) && $this->elements[3] != "") $sk = $this->elements[3];
		
		$this->value = $_SERVER[$sk];
		
		$email_elements[$this->getName()] = stripslashes($this->value);
		if (!isset($this->elements[2]) || $this->elements[2] != "no_db") $sql_elements[$this->getName()] = $this->getValue();
	}
	
	function getDescription()
	{
		return "ip -> Beispiel: ip|label|[no_db]";
	}
}

?>