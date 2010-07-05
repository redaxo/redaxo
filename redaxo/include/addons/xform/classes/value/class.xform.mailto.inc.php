<?php

class rex_xform_mailto extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
	
		// mailto als referenz auf anderes input feld (muss vor dem mailto feld stehen!)
		if(isset($email_elements[$this->elements[1]]))
		{
		
			$this->params["mail_to"] = $email_elements[$this->elements[1]];
			// Aus Spam gründen keine Zeilenumbrüche zulassen
			$this->params["mail_to"] = str_replace(array("\n", "\r\n", "\r"), '', $this->params["mail_to"]);
		}else
		{
			// direkt angegebene Emailadresse
			$this->params["mail_to"] = $this->elements[1];
		}
	
	}
	
	function getDescription()
	{
		return "	mailto -> Beispiel: mailto|email@domain.de 
			<br />	mailto -> Beispiel:mailto|usr_email (Verweis auf vorhergendes Eingabefeld)";
	}
}

?>