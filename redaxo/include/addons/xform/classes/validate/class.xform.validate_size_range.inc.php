<?php

class rex_xform_validate_size_range extends rex_xform_validate_abstract 
{

	function enterObject(&$warning, $send, &$warning_messages)
	{
		if($send=="1")
		{		
			
			// Wenn leer, dann alles ok
			if($this->obj_array[0]->getValue() == "")
				return;
			
			$w = FALSE;
			
			$minsize = -1;
			if($this->elements[3] != "")
				$minsize = (int) $this->elements[3];

			$maxsize = -1;
			if($this->elements[4] != "")
				$maxsize = (int) $this->elements[4];
				
			$size = strlen($this->obj_array[0]->getValue());
			
			if($minsize > -1 && $minsize > $size)
				$w = TRUE;

			if($maxsize > -1 && $maxsize < $size)
				$w = TRUE;
				
			if($w)
			{
				$warning["el_".$this->obj_array[0]->getId()]=$this->params["error_class"];
				$warning_messages[] = $this->elements[5];
			}
		}
	}
	
	function getDescription()
	{
		return "size_range -> Laenge der Eingabe muss mindestens und/oder maximal sein, beispiel: validate|size_range|label|[minsize]|[maxsize]|Fehlermeldung";
	}
}