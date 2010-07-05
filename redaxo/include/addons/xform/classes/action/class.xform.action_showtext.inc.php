<?php

class rex_xform_action_showtext extends rex_xform_action_abstract
{
	
	function execute()
	{
	
		$text = "";
		if (isset($this->action["elements"][3])) 
			$text .= $this->action["elements"][3];
		if (isset($this->action["elements"][2])) 
			$text .= $this->action["elements"][2];
		if (isset($this->action["elements"][4])) 
			$text .= $this->action["elements"][4];
		if ($text == "") 
			$text = $this->params["answertext"];

		foreach ($this->elements_email as $search => $replace)
		{
			$text = str_replace('###'. $search .'###', $replace, $text);
		}

		$this->params["output"] = $text;
	}

	function getDescription()
	{
		return "action|showtext|Antworttext|<p>|</p>";
	}

}

?>