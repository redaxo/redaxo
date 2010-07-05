<?php

/**
 * Klasse
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_com
{

	function formatText($text)
	{
	
		// Zitate			[quote]wieder[/quote]
		// Fett				[b]ermöglichen[/b]
		// Durchstrichen	[strike]text[/strike]
		// Unterstrichen	[u]underline[/u]
		// Smilies			:) ;) ...
		// url				[url]www.web.de[/url]
		
		return $text;
	
	}








	// Mein
	
	function user_getName($format = "html")
	{
		global $REX;
		if(!isset($REX['COM_USER']))
			return '';
		
		if($format == "html")
			return '<span class="rex_com_user"><b>'.$REX['COM_USER']->getValue('firstname').' '.$REX['COM_USER']->getValue('name').'</b></span>';
		else
			return $REX['COM_USER']->getValue('firstname').' '.$REX['COM_USER']->getValue('name');

	}


	function debug($a)
	{
		echo '<pre>';
		var_dump($a);
		echo '</pre>';
	}



}
?>