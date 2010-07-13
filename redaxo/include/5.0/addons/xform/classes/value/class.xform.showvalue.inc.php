<?php

class rex_xform_showvalue extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{

		// $this->value = $GU->getValue($this->elements[1]);

		if ($this->value == "" && !$send && isset($this->elements[3]))
		{
			$this->value = $this->elements[3];
		}

		// hidden muss drin sein, da bei disabled felder die werte nicht übertragen werden

		$form_output[] = '
			<p class="formtext">
			<label class="text" for="el_'.$this->id.'">'.$this->elements[2].'</label>
			<input type="hidden" name="FORM['.$this->params["form_name"].'][el_'.$this->id.']" value="'.htmlspecialchars(stripslashes($this->value)).'" />
			<input type="text" class="inp_disabled" disabled="disabled" id="el_'.$this->id.'" value="'.htmlspecialchars(stripslashes($this->value)).'" />
			</p>';

		$email_elements[$this->elements[1]] = stripslashes($this->value);
		// if ($this->elements[4] != "no_db") $sql_elements[$this->elements[1]] = $this->value;
	}
	
	function getDescription()
	{
		return "showvalue -> Beispiel: showvalue|login|Loginname|defaultwert";
	}
}

?>