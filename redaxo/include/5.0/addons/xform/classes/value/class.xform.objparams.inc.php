<?php

class rex_xform_objparams extends rex_xform_abstract
{

	function init()
	{
		$vals = explode("#",trim($this->elements[2]));
		if (count($vals)>1)
		{
			$this->params[trim($this->elements[1])] = array();
			foreach($vals as $val)
			{
				$this->params[trim($this->elements[1])][] = $val;
			}
		}else
		{
			$this->params[trim($this->elements[1])] = trim($this->elements[2]);
		}
	}

	
	function enterObject(&$email_elements,&$sql_elements,&$warning,&$form_output,$send = 0)
	{	

	}
	
	
	function getDescription()
	{
		return "objparams -> Beispiel: objparams|key|newvalue";
	}

}

?>