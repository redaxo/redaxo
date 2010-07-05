<?php

class rex_xform_validate_type extends rex_xform_validate_abstract
{

	function enterObject(&$warning, $send, &$warning_messages)
	{
		if($send=="1")
		{
			$Object=$this->obj_array[0];
			
			// Wenn Feld leer ist - auch ok
			if(isset($this->elements[5]) && $this->elements[5] == 1 && $Object->getValue() == "")
				return;
			
			$w = FALSE;
			
			switch(trim($this->elements[3]))
			{
				case "int":
									$xsRegEx_int = "/^[0-9]+$/i";
									
									if(preg_match($xsRegEx_int, $Object->getValue())==0)
										$w = TRUE;
									break;
									
				case "float":
									$xsRegEx_float = "/^([0-9]+|([0-9]+\.[0-9]+))$/i";
									
									if(preg_match($xsRegEx_float, $Object->getValue())==0)
										$w = TRUE;
										
									break;
									
				case "numeric":
									if(!is_numeric($Object->getValue()))
										$w = TRUE;
										
									break;
				case "string":	
									break;
				case "email":	
									$xsRegEx_email = "/^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,6}$/i";
									
									if(preg_match($xsRegEx_email, $Object->getValue())==0)
										$w = TRUE;
									
									break;
				case "url":
									$xsRegEx_url = '/^(?:http:\/\/)[a-zA-Z0-9][a-zA-Z0-9._-]*\.(?:[a-zA-Z0-9][a-zA-Z0-9._-]*\.)*[a-zA-Z]{2,5}(?:\/[^\\/\:\*\?\"<>\|]*)*(?:\/[a-zA-Z0-9_%,\.\=\?\-#&]*)*$'."/'";
									
									if(preg_match($xsRegEx_url, $Object->getValue())==0)
										$w = TRUE;
									
									break;
				case "date":
									$xsRegEx_datum = "/^([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{2,4})$/i";
									
									if(preg_match($xsRegEx_datum, $Object->getValue())==0)
										$w = TRUE;
										
									break;
									
				case "":
									break;
				default:			
									echo "Type ".$this->elements[3]." nicht definiert";
									$w = TRUE;
									break;
			}
			
			if ($w)
			{ 
					$warning["el_" . $Object->getId()]=$this->params["error_class"];
					$warning_messages[] = $this->elements[4];
			
			}
					
		}
	}
	
	
	function getDescription()
	{
		return "type -> prüft auf typ,beispiel: validate|type|plz|int(oder string,float,email,url,date)|Fehlermeldung|[1= Feld darf auch leer sein]";
	}
	
	function getLongDescription()
	{
		return "Hiermit lassen sich verschiedenste Typen prüfen. von int/float/numeric/string/email/url bis zu date. mit dem letzen optionalen Parameter kann man definieren ob ein leerer Wert akzeptiert wird.";
	}
	
}