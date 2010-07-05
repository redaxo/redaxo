<?php

class rex_xform_validate_labelexist extends rex_xform_validate_abstract 
{

	function enterObject(&$warning, $send, &$warning_messages)
	{
		if($send=="1")
		{
		
			// optional, ein oder mehrere felder müssen ausgefüllt sein
			if(!isset($this->elements[3]) || $this->elements[3] == "")
				$minamount = 1;
			else
				$minamount = (int) $this->elements[3];

			if(!isset($this->elements[4]) || $this->elements[4] == "")
				$maxamount = 1000;
			else
				$maxamount = (int) $this->elements[4];


			// labels auslesen
			$fields = explode(",",$this->elements[2]);
			
			$value = 0;
			foreach($this->Objects as $o)
			{
				if (in_array($o->getDatabasefieldname(),$fields) && $o->getValue() != "") 
					$value++;
			}

			if ($value < $minamount || $value > $maxamount)
			{
				$warning_messages[] = $this->elements[5];
				
				foreach($this->Objects as $o)
				{
					if (in_array($o->getDatabasefieldname(),$fields))
					{
						$warning["el_" . $o->getId()] = $this->params["error_class"];
					}
				}
			}
		}
	}
	
	function getDescription()
	{
		return "labelexist -> mindestens ein feld muss ausgefüllt sein, example: validate|labelexist|label,label2,label3|[minlabels]|[maximallabels]|Fehlermeldung";
	}
}