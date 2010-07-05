<?php

class rex_xform_action_encrypt_value extends rex_xform_action_abstract
{

	function execute()
	{
		
		$l = $this->action["elements"][2];
		$f = $this->action["elements"][3];
	
		// Array mit Content für DB
		// $this->elements_sql
	
		// Array mit Content für E-Mail
		// $this->elements_email
	
		foreach($this->elements_sql as $key => $value)
		{
			if($l == $key)
			{
				$this->elements_sql[$key] = $f($value);
			}
		}
	
	
		foreach($this->elements_email as $key => $value)
		{
			if($l == $key)
			{
				$this->elements_email[$key] = $f($value);
			}
		}

		return;

	}

	function getDescription()
	{
		return "action|encrypt|label|md5";
	}

}

?>