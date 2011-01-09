<?php

class rex_xform_password extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{		
		if ($this->value == "" && !$send)
		{
			if (isset($this->elements[3])) $this->value = $this->elements[3];
		}

		$wc = "";
		if (isset($warning["el_" . $this->getId()])) $wc = $warning["el_" . $this->getId()];

		$form_output[] = '
				<p class="formpassword">
				<label class="password ' . $wc . '" for="el_' . $this->id . '" >' . $this->elements[2] . '</label>
				<input type="password" class="password ' . $wc . '" name="FORM[' . 
				$this->params["form_name"] . '][el_' . $this->id . ']" id="el_' . $this->id . '" value="' . 
				htmlspecialchars(stripslashes($this->value)) . '" />
				</p>';
		$email_elements[$this->elements[1]] = stripslashes($this->value);
		if (!isset($this->elements[4]) || $this->elements[4] != "no_db") $sql_elements[$this->elements[1]] = $this->value;
	}
	
	function getDescription()
	{
		return "password -> Beispiel: password|psw|Passwort|default_value|[no_db]";
	}
}

?>