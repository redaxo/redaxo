<?php

class rex_xform_php extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		ob_start();
		eval("?>".$this->elements[1]);
		$out = ob_get_contents();
		ob_end_clean();

		$form_output[] = $out;
	}
	
	function getDescription()
	{
		return htmlspecialchars(stripslashes('php -> Beispiel: php|<?php echo date("mdY"); ?>'));
	}
}

?>