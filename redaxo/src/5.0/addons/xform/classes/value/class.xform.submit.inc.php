<?php

class rex_xform_submit extends rex_xform_abstract
{

	function preAction()
	{
		$this->params["submit_btn_show"] = FALSE; // ist referenz auf alle parameter.
	}

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{	
		$this->value = $this->elements[2];

		$css_class = "";
		if (isset($this->elements[4]) && $this->elements[4] != "") $css_class = $this->elements[4];
	
		$wc = $css_class;
		if (isset($warning["el_" . $this->getId()])) $wc = $warning["el_" . $this->getId()]." ";
	
       	$form_output[] = '
				<p class="formsubmit formlabel-'.$this->getName().'">
				<input type="submit" class="submit ' . $wc . '" name="FORM['.$this->params["form_name"] . '][el_' . $this->getId() . ']" id="el_' . $this->getId() . '" value="' . 
				htmlspecialchars(stripslashes($this->getValue())) . '" />
				</p>';
		$email_elements[$this->elements[1]] = stripslashes($this->getValue());
		if (!isset($this->elements[3]) || $this->elements[3] != "no_db") $sql_elements[$this->elements[1]] = $this->getValue();
		
		$this->params["submit_btn_show"] = FALSE;
	}
	
	function getDescription()
	{
		return "submit -> Beispiel: submit|label|value|[no_db]|cssclassname";
	}
}

?>