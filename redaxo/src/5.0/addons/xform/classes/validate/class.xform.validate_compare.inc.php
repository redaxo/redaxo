<?PHP

// TODO :: GEHT NOCH NICHT

class rex_xform_validate_compare extends rex_xform_validate_abstract 
{

	function enterObject(&$warning, $send, &$warning_messages)
	{
		if($send=="1")
		{
			$field_1 = $this->elements[2];
			$field_2 = $this->elements[3];
			foreach($this->Objects as $o)
			{
				if ($o->getDatabasefieldname() == $field_1)
				{
					$id_1 = $o->getId(); 
					$value_1 = $o->getValue();
				}
				if ($o->getDatabasefieldname() == $field_2)
				{
					$id_2 = $o->getId(); 
					$value_2 = $o->getValue();
				}
			}
			if ($value_1 != $value_2)
			{
				$warning["el_" . $id_1] = $this->params["error_class"];
				$warning["el_" . $id_2] = $this->params["error_class"];
				$warning_messages[] = $this->elements[4];
			}
		}
	}
	
	function getDescription()
	{
		return "compare -> prüft ob leer, beispiel: validate|compare|label1|label2|warning_message ";
	}
}

?>