<?php

class rex_xform_standard_packages extends rex_xform_abstract
{

	function init()
	{	

		$REPL = array();
		
		// ------------------ PACKAGES

		$REPL["contactform"] = 
'text|Vorname|Vorname *:
validate|notEmpty|Vorname|Bitte geben Sie Ihren Vornamen ein
text|Nachname|Nachname *:
validate|notEmpty|Nachname|Bitte geben Sie Ihren Nachnamen ein
text|Strasse|Straße *:
validate|notEmpty|Strasse|Bitte geben Sie Ihre Strasse ein
text|Hausnummer|Hausnummer *:
validate|notEmpty|Hausnummer|Bitte geben Sie Ihre Hausnummer ein
text|Zusatz|Zusatz:
text|PLZ|PLZ *:
validate|notEmpty|PLZ|Bitte geben Sie Ihre PLZ ein
text|Stadt|Stadt *:
validate|notEmpty|Stadt|Bitte geben Sie Ihre Stadt ein
textarea|betreff|Wie können wir Ihnen helfen';


		// ------------------ / PACKAGES


		// ----- wenn falscher aufruf return
		if (!isset($this->elements[1])) return;
		if (!array_key_exists($this->elements[1],$REPL)) return;
		
		// ----- neuen array generieren und einsetzen		
		$form_elements_tmp = explode("\n", $REPL[$this->elements[1]]);
		$form_elements_add = array();
		foreach($form_elements_tmp as $form_element)
		{
			if(trim($form_element)!="") $form_elements_add[] = $form_element;
		}

		// an der richtigen Stelle einsetzen
		// $this->params["form_elements"][]
		
		$new_array = array();
		for ($i = 0; $i < count($this->params["form_elements"]); $i++)
		{
			if ($this->id == $i)
			{
				// muss gesetzt werden, da sonst der erste, 
				// der ja gleichzeit das packages ist, nicht mehr aufgerufen wird.
				$new_array[] = ""; 
				foreach($form_elements_add as $new_add)
				{
					$new_array[] = $new_add;
				}
			}else
			{
				$new_array[] = $this->params["form_elements"][$i];
			}
		}

		// ----- neuen array einsetzen.
		$this->params["form_elements"] = $new_array;

	}
	
	function getDescription()
	{
		return "standard_packages -> Beispiel: standard_packages|contactform";
	}
}

?>