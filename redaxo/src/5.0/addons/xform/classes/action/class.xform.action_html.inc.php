<?php

class rex_xform_action_html extends rex_xform_action_abstract
{
	
	function execute()
	{
	
		$html = $this->action["elements"][2];
		echo $html;

		return TRUE;
	}

	function getDescription()
	{
		return "action|html|&lt;b&gt;fett&lt;/b&gt;";
	}

}

?>