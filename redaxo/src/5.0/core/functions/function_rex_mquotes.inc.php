<?php

/** 
 * Function to compensate the deprecated magic_quotes_gpc setting
 *   
 * @package redaxo4 
 * @version svn:$Id$ 
 */

if (get_magic_quotes_gpc())
{
	function removeSlashesOnArray(&$theArray)
	{
		if (is_array($theArray))
		{
			reset($theArray);
			while(list($Akey,$AVal)=each($theArray))
			{
				if (is_array($AVal))
				{
					removeSlashesOnArray($theArray[$Akey]);
				}else
				{
					$theArray[$Akey] = stripslashes($AVal);
				}
			}
			reset($theArray);
		}
	}
	
	if (is_array($_GET))
	{
	    removeSlashesOnArray($_GET);
	}
	
	if (is_array($_POST))
	{
	    removeSlashesOnArray($_POST);
	}
	
	if (is_array($_REQUEST))
	{
	    removeSlashesOnArray($_REQUEST);
	}
}
