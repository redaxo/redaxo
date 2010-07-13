<?php

class rex_xform_validate_abstract
{
	var $params = array();
	var $obj;
	var $elements;
  var $obj_array;
	
	var $Objects; // die verschiedenen Value Objekte
	
	function loadParams(&$params, &$elements)
	{
		$this->params = &$params;
	  $this->elements = $elements;
	}
	
	function setObjects($Objects)
	{

		$this->obj = $Objects;
		$tmp_Objects = explode(",", $this->elements[2]);
		
		foreach($tmp_Objects as $tmp_Object)
		{
			$tmp_FoundObject=false;
			foreach($Objects as $Object)
			{
				if(strcmp($Object->getDatabasefieldname(),trim($tmp_Object))==0)
				{
					$this->obj_array[] = &$Object;
					$tmp_FoundObject = true;
					break;
				}
			}
			if(!$tmp_FoundObject)
				echo "FEHLER: Object ".$tmp_FoundObject." nicht gefunden!";
		}
		
		// deprecated
		$this->Objects = $Objects; 
		
	}
	
	function enterObject()
	{
		
	}
	
	function getDescription()
	{
		return "Für dieses Objekt fehlt die Beschreibung";
	}

	function getLongDescription()
	{
		return "Für dieses Objekt fehlt die Beschreibung";
	}
	
	function getDefinitions()
	{
		return array();
	}
	
}