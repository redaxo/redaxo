<?php

class rex_xform_action_fulltext_value extends rex_xform_action_abstract
{

	function execute()
	{
		$label = $this->action["elements"][2];
		$labels = " ,".$this->action["elements"][3].",";

		$vt = "";
		foreach($this->elements_sql as $key => $value)
		{
			if (strpos($labels,",$key,")>0)
			{
				// echo "<br >$key:  $value";
				$this->elements_sql[$label] .= " ".$value;
			}
		}
		// echo "<br /><br />$label: ".$this->elements_sql[$label];
		
		return;

	}

	function getDescription()
	{
		return "action|fulltext_value|label|fulltextlabels with ,";
	}

}

?>