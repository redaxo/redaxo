<?PHP

class rex_xform_validate_empty extends rex_xform_validate_abstract 
{

	function enterObject(&$warning, $send, &$warning_messages)
	{
		if($send=="1")
		{
			foreach($this->obj_array as $Object)
			{
				// echo '<p>Wert wird überprüft:';
				// echo "val: id:".$xoObject->getId()." value:".$xoObject->getValue()." elements:".print_r($xoObject->elements);
				// echo '</p>';
			
				if($Object->getValue() == "")
				{
					$warning["el_" . $Object->getId()] = $this->params["error_class"];
					if (!isset($this->elements[3])) $this->elements[3] = "";
					$warning_messages[] = $this->elements[3];
				}
			}
		}
	}
	
	function getDescription()
	{
		return "empty -> prüft ob leer, beispiel: validate|empty|label|warning_message ";
	}
}
?>