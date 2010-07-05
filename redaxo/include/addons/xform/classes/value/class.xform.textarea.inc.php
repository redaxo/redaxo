<?php

class rex_xform_textarea extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{		
		if ($this->value == "" && !$send)
		{
			if (isset($this->elements[3]))
			{
				$this->setValue($this->elements[3]);
			}
		}

		$classes = "";
    if (isset($this->elements[5]))
    {
      $classes .= " ".$this->elements[5];
    }
		
		$wc = "";
		if (isset($warning["el_" . $this->getId()]))
		{
			$wc = " ".$warning["el_" . $this->getId()];
		}
		
		$form_output[] = '
		<p class="formtextarea">
			<label class="textarea ' . $wc . '" for="el_' . $this->id . '" >' . $this->elements[2] . '</label>
			<textarea class="textarea' . $classes . $wc . '" name="FORM[' . $this->params["form_name"] . '][el_' . $this->getId() . ']" id="el_' . $this->getId() . '" cols="80" rows="10">' . htmlspecialchars(stripslashes($this->value)) . '</textarea>
		</p>';

		$email_elements[$this->getName()] = stripslashes($this->value);
		if (!isset($this->elements[4]) || $this->elements[4] != "no_db")
		{
			$sql_elements[$this->getName()] = $this->getValue();
		}
	}
	
	function getDescription()
	{
		return "textarea -> Beispiel: textarea|label|FieldLabel|default|[no_db]";
	}
	
	function getDefinitions()
	{
    return array(
            'type' => 'value',
            'name' => 'textarea',
            'values' => array(
	              array( 'type' => 'name',   'label' => 'Feld' ),
	              array( 'type' => 'text',    'label' => 'Bezeichnung'),
	              array( 'type' => 'text',    'label' => 'Defaultwert'),
	              array( 'type' => 'no_db',   'label' => 'Datenbank',  'default' => 1),
	              array( 'type' => 'text',    'label' => 'classes'),
              ),
            'description' => 'Ein mehrzeiliges Textfeld als Eingabe',
            'dbtype' => 'text'
      );
	}
}

?>