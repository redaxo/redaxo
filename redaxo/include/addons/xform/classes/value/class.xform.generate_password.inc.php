<?php

class rex_xform_generate_password extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		$this->value = substr(md5(microtime()), 0, 6);
		$email_elements[$this->elements[1]] = stripslashes($this->value);
		if ($this->elements[2] != "no_db") $sql_elements[$this->elements[1]] = $this->value;
	}
	
	function getDescription()
	{
		return "generate_passwort -> Beispiel: generate_password|password|[no_db]";
	}
}

?>