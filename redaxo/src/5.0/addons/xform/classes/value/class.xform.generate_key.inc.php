<?php

class rex_xform_generate_key extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		$this->value = md5($this->params["form_name"] . substr(md5(microtime()), 0, 6));
		$form_output[] = '';

		$email_elements[$this->elements[1]] = stripslashes($this->value);
		if (!isset($element[2]) || $element[2] != "no_db") $sql_elements[$this->elements[1]] = $this->value;


	}
	
	function getDescription()
	{
		return "generate_key -> Beispiel: generate_key|activation_key|[no_db]";
	}
}

?>