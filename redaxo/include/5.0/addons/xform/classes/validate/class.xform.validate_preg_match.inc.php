<?php

class rex_xform_validate_preg_match extends rex_xform_validate_abstract
{

	function enterObject(&$warning, $send, &$warning_messages)
	{
		if($send=="1")
		{
			 
			$pm = $this->elements[3];
			 
			foreach($this->obj_array as $Object)
			{
				 
				preg_match($pm, $Object->getValue(), $matches);

				if(count($matches) > 0 && current($matches) == $Object->getValue())
				{

				}else
				{
					if (!isset($this->elements[4]))
					{
						$this->elements[4] = "";
					}
					$warning["el_" . $Object->getId()] = $this->params["error_class"];
					$warning_messages[] = $this->elements[4];
				}
				 
			}
		}
	}

	function getDescription()
	{
		return "preg_match -> prueft Ÿber preg_match, beispiel: validate|preg_match|label|/[a-z]/i|warning_message ";
	}
}
?>