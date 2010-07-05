<?php

class rex_xform_datestamp extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{
		$format = "Y-m-d";
		if ($this->elements[2] != "")
		{
			$format = $this->elements[2];
		}
  
		 // 0 = immer setzen, 1 = nur wenn leer / create
		if(!isset($this->elements[4]) || $this->elements[4] != 1)
		{
			$set = 0;
		}else
		{
		  $set = 1;
		}

		if($this->getValue() == "" || $set == 0)
		{
			$this->value = date($format);
		}
		$form_output[] = '
      <p class="formhidden formlabel-'.$this->getName().'" style="display:hidden;">
        <input type="hidden" name="FORM['.$this->params["form_name"].'][el_'.$this->getId().']" id="el_'.$this->getId().'" value="'.
		htmlspecialchars(stripslashes($this->getValue())).'" />
      </p>';

		$email_elements[$this->getName()] = $this->getValue();
		if (!(isset($this->elements[3]) && $this->elements[3] == "no_db"))
		{
			$sql_elements[$this->getName()] = $this->getValue();
		}
	}

	function getDescription()
	{
		return "datestamp -> Beispiel: datestamp|label|[Y-m-d]|[no_db]|[0-wird immer neu gesetzt,1-nur wenn leer]";
	}

	function getDefinitions()
	{

		return array(
            'type' => 'value',
            'name' => 'datestamp',
            'values' => array(
		array( 'type' => 'name',   'label' => 'Name' ),
		array( 'type' => 'text',    'label' => 'Format [YmdHis]'),
		array( 'type' => 'no_db',   'label' => 'Datenbank',  'default' => 1),
		array( 'type' => 'select',  'label' => 'Wann soll Wert gesetzt werden', 'default' => '0', 'definition' => 'immer=0;nur wenn leer=1' ),
		),

            'description' => 'Ein Selectfeld mit festen Definitionen.',
            'dbtype' => 'varchar(255)'
            );


	}


}