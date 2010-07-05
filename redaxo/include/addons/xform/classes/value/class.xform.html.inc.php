<?php

class rex_xform_html extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		$form_output[] = $this->elements[1];
	}
	
	function getDescription()
	{
		return htmlspecialchars(stripslashes('html -> Beispiel: html|<div class="block">'));
	}
}

?>