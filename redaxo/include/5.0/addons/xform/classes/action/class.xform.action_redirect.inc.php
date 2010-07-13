<?php

class rex_xform_action_redirect extends rex_xform_action_abstract
{

	function execute()
	{

		$element_2 = $this->action["elements"][2];
		
		$url = '';
		if (preg_match('/^[0-9]+$/i',$element_2)) {
			$url = rex_getUrl($element_2,'','',"&");
		}
		elseif ($element_2 != '') {
			$url = $element_2;
		}

		// Emailkeys ersetzen. Somit auch Weiterleitungen mit neuer ID mšglich. "id=###ID###"
		foreach ($this->elements_email as $search => $replace)
		{
			$url = str_replace('###'. $search .'###', $replace, $url);
		}

		if ($url != '') {
			ob_end_clean();
			header("Location: ".$url);
			exit;
		}

	}

	function getDescription()
	{
		return "action|redirect|Artikel-Id oder Externer Link";
	}

}

?>
