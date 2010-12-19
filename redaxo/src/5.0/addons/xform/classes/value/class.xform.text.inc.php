<?php

class rex_xform_text extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{

		if ($this->getValue() == "" && !$send)
		{
			if (isset($this->elements[3])) $this->setValue($this->elements[3]);
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
			<p class="formtext formlabel-'.$this->getName().'">
				<label class="text' . $wc . '" for="el_' . $this->getId() . '" >' . $this->elements[2] . '</label>
				<input type="text" class="text' . $classes. $wc . '" name="FORM[' . 
		$this->params["form_name"] . '][el_' . $this->id . ']" id="el_' . $this->id . '" value="' .
		htmlspecialchars(stripslashes($this->getValue())) . '" />
			</p>';
		$email_elements[$this->elements[1]] = stripslashes($this->getValue());
		if (!isset($this->elements[4]) || $this->elements[4] != "no_db")
		{
			$sql_elements[$this->elements[1]] = $this->getValue();
		}
	}

	function getDescription()
	{
		return "text -> Beispiel: text|label|Bezeichnung|defaultwert|[no_db]";
	}

	function getDefinitions()
	{
		return array(
						'type' => 'value',
						'name' => 'text',
						'values' => array(
									array( 'type' => 'name',   'label' => 'Feld' ),
									array( 'type' => 'text',    'label' => 'Bezeichnung'),
									array( 'type' => 'text',    'label' => 'Defaultwert'),
									array( 'type' => 'no_db',   'label' => 'Datenbank',  'default' => 1),
		              array( 'type' => 'text',    'label' => 'classes'),
        		),
						'description' => 'Ein einfaches Textfeld als Eingabe',
						'dbtype' => 'text'
						);

	}
}

?>