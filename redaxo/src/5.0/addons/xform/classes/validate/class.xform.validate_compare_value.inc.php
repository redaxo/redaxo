<?PHP

class rex_xform_validate_compare_value extends rex_xform_validate_abstract 
{

	function enterObject(&$warning, $send, &$warning_messages)
	{
		if($send=="1")
		{
			$field = $this->elements[2];
			$value = -1;
			foreach($this->Objects as $o)
			{
				if ($o->getDatabasefieldname() == $field) $value = $o->getValue();
			}
			if ($value === -1 || strtolower($value) != strtolower($this->elements[3]))
			{
					$warning_messages[] = $this->elements[4];
			}
		}
	}
	
	function getDescription()
	{
		return "compare_value -> compare label with value, example: validate|compare_value|label|value|warning_message ";
	}
}

?>