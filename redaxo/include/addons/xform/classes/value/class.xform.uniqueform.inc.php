<?php

class rex_xform_uniqueform extends rex_xform_abstract
{

	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{	
	
		$table = $this->elements[2];
	
		// ***** ERSTER AUFRUF -> key erstellen
		if (!$send)
		{
			$this->value = md5($_SERVER["REMOTE_ADDR"].time());

		}else
		{
			// in tabelle nachsehen ob formcode vorhanden
			$sql = 'select '.$this->getName().' from '.$table.' WHERE '.$this->getName().'="'.$this->getValue().'" LIMIT 1';
			$cd = rex_sql::factory();
			if ($this->params["debug"]) $cd->debugsql = true;
			$cd->setQuery($sql);
			if ($cd->getRows()==1)
			{
				$this->params["warning"][] = $this->elements[3];
				$this->params["warning_messages"][] = $this->elements[3];
			}
	
		}
	
		$form_output[] = '<input type="hidden" name="FORM['.$this->params["form_name"].'][el_'.$this->getId().']" value="'.htmlspecialchars(stripslashes($this->getValue())).'" />';

		$email_elements[$this->getName()] = stripslashes($this->getValue());
		$sql_elements[$this->getName()] = stripslashes($this->getValue());
	
		return;
	
	}
	
	function getDescription()
	{
		return "uniqueform -> Beispiel: uniqueform|label|table|Fehlermeldung";
	}
}

?>