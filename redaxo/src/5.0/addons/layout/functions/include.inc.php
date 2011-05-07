<?php

/**
 * Layout 
 *
 * @author jan[dot]kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @author thomas[dot]blum[at]redaxo[dot]de Thomas Blum
 * @author <a href="http://www.blumbeet.com">www.blumbeet.com</a>
 *
 */

/*
 * Add markup in the page header
*/
function rex_epIncludePageHeader($params)
{	
	if (isset($params['markup']))
		$params['subject'] = $params['subject'].$params['markup'];


	return $params['subject'];
}


function rex_includeCss($markup)
{
	rex_register_extension('PAGE_HEADER', 'rex_epIncludePageHeader', array('markup' => $markup));
}


function rex_includeJavascript($markup)
{
	rex_register_extension('PAGE_HEADER', 'rex_epIncludePageHeader', array('markup' => $markup));
}



/*
 * Add an attribute to <body> tag
*/
function rex_epAddBodyAttribute($params)
{
	
	if (isset($params['attributes']))
	{
		foreach ($params['attributes'] as $attribute => $value)
		{
			$params['subject'][$attribute][] = $value;
		}
	}
	
	return $params['subject'];
}


function rex_addBodyClass($class)
{
	rex_addBodyAttribute('class', $class);
}


function rex_addBodyAttribute($attribute, $value)
{
	rex_register_extension('PAGE_BODY_ATTR', 'rex_epAddBodyAttribute', array('attributes' => array($attribute => $value)));
}