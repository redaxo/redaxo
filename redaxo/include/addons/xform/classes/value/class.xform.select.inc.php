<?php

class rex_xform_select extends rex_xform_abstract
{

	/*
	 * Werte setzen
	 */
	function init()
	{
		foreach (explode(";", $this->elements[3]) as $v)
		{
			$teile = explode("=", $v);
			$wert = $teile[0];
			if (is_array($teile) && isset ($teile[1]))
			{
				$bezeichnung = $teile[1];
			}else
			{
				$bezeichnung = $teile[0];
			}
			$this->setKey($bezeichnung,$wert);
		}
	} 

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
	
		$multiple = FALSE;
		if(isset($this->elements[6]) && $this->elements[6]==1)
			$multiple = TRUE;
	
		$SEL = new rex_select();
		$SEL->setId("el_" . $this->getId());
		if($multiple)
		{
			$SEL->setName("FORM[" . $this->params["form_name"] . "][el_" . $this->getId() . "][]");
			$SEL->setSize(5);
			$SEL->setMultiple(1);
			if(!is_array($this->getValue()))
				$this->value = array();
		}else
		{
			$SEL->setName("FORM[" . $this->params["form_name"] . "][el_" . $this->getId() . "]");
			$SEL->setSize(1);
			$this->value = stripslashes($this->getValue());	}

		foreach($this->getKeys() as $k => $v)
		{
			$SEL->addOption($v, $k);
		}

		if ($this->getValue() == "" && !$send)
		{
			if (isset($this->elements[5])) $SEL->setSelected($this->elements[5]);
		}else
		{
			if (is_array($this->getValue()))
			{
				foreach($this->value as $val) $SEL->setSelected($val);
			}else
			{
				$SEL->setSelected($this->getValue());
			}
		}

		$wc = "";
		if (isset($warning["el_" . $this->getId()])) $wc = $warning["el_" . $this->getId()];
		$SEL->setStyle(' class="select ' . $wc . '"');

		$form_output[] = ' 
			<p class="formselect formlabel-'.$this->getName().'">
			<label class="select ' . $wc . '" for="el_' . $this->id . '" >' . $this->elements[2] . '</label>' . 
			$SEL->get() . '
			</p>';

		$email_elements[$this->elements[1]] = $this->getValue();
		if (!isset($this->elements[4]) || $this->elements[4] != "no_db") $sql_elements[$this->elements[1]] = $this->getValue();

	}
	
	function getDescription()
	{
		return "select -> Beispiel: select|gender|Geschlecht *|Frau=w;Herr=m|[no_db]|defaultwert|multiple=1";
	}
	
  function getDefinitions()
  {
    return array(
            'type' => 'value',
            'name' => 'select',
            'values' => array(
              array( 'type' => 'name',   'label' => 'Feld' ),
              array( 'type' => 'text',    'label' => 'Bezeichnung'),
              array( 'type' => 'text',  	'label' => 'Selektdefinition',   'example' => 'Frau=w;Herr=m'),
              array( 'type' => 'no_db',   'label' => 'Datenbank',          'default' => 1),
              array( 'type' => 'text',    'label' => 'Defaultwert'),
              // array( 'type' => 'boolean', 'label' => 'Mehrfachselektion',  'default' => 0),
            ),
            'description' => 'Ein Selektfeld mit festen Definitionen',
            'dbtype' => 'text'
      );      
      
  }
	
	
}

?>