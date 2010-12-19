<?php

class rex_xform_action_copy_value extends rex_xform_action_abstract
{

	function execute()
	{
		foreach($this->elements_sql as $key => $value)
		{
			if ($this->action["elements"][2]==$key)
			{
				$tmp_value = $value;
				break;
			}
		}

		
		foreach($this->elements_sql as $key => $value)
		{
			if ($this->action["elements"][3]==$key)
			{
				$this->elements_sql[$key] = $tmp_value;
			}
		}
		
		return;

	}

	function getDescription()
	{
		return "action|copyvalue|label_from|label_to";
	}

}

?>