<?php

/**
 * Function to compensate the deprecated magic_quotes_gpc setting
 *
 * @package redaxo5
 * @version svn:$Id$
 */

if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
{
	function rex_magic_quotes_gpc_strip(&$value)
	{
		$value = is_array($value) ? array_map('rex_magic_quotes_gpc_strip', $value) : stripslashes($value);
		return $value;
	}
	$_GET = rex_magic_quotes_gpc_strip($_GET);
	$_POST = rex_magic_quotes_gpc_strip($_POST);
	$_COOKIE = rex_magic_quotes_gpc_strip($_COOKIE);
	$_REQUEST = rex_magic_quotes_gpc_strip($_REQUEST);
}