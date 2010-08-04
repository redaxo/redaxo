<?php
/**
 * TinyMCE Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @author andreas[dot]eberhard[at]redaxo[dot]de Andreas Eberhard
 * @author <a href="http://rex.andreaseberhard.de">rex.andreaseberhad.de</a>
 *
 * @author Dave Holloway
 * @author <a href="http://www.GN2-Netwerk.de">www.GN2-Netwerk.de</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

if (!isset($REX['FILEPERM'])) 
{
	$REX['FILEPERM'] = octdec(664); // oktaler wert
}
if (!isset($REX['DIRPERM'])) 
{
	$REX['DIRPERM'] = octdec(775); // oktaler wert
}

/**
 * Für Kompatibilität mit Modulen REDAXO 3.1.x / 4.x mit TinyMCE 2
 */
class rexTiny2Editor extends rexTinyMCEEditor
{
}

/**
 * für REDAXO 3.2.x
 */
if (!function_exists('rex_info'))
{
	function rex_info($msg)
	{
		return '<p class="rex-warning" style="padding:7px; width:756px; background-color:#D2EFD9; color:#107C6C;">' . $msg . '</p>';
	}
} // End function_exists

/**
 * für REDAXO 3.2.x
 */
if (!function_exists('rex_request'))
{
	function rex_request($varname, $vartype = '', $default = '')
	{
		return _rex_array_key_cast($_REQUEST, $varname, $vartype, $default);
	}
} // End function_exists

/**
 * für REDAXO 3.2.x
 */
if (!function_exists('_rex_array_key_cast'))
{
	function _rex_array_key_cast($haystack, $needle, $vartype, $default = '')
	{
		if(!is_array($haystack))
		{
			trigger_error('Array expected for $haystack in _rex_array_key_cast()!', E_USER_ERROR);
			exit();
		}

		if(!is_scalar($needle))
		{
			trigger_error('Scalar expected for $needle in _rex_array_key_cast()!', E_USER_ERROR);
			exit();
		}

		if(array_key_exists($needle, $haystack))
		{
		$var = $haystack[$needle];
			return _rex_cast_var($var, $vartype);
		}

		return _rex_cast_var($default, $vartype);
	}
} // End function_exists

/**
 * für REDAXO 3.2.x
 */
if (!function_exists('_rex_cast_var'))
{
	function _rex_cast_var($var, $vartype)
	{
		if(!is_string($vartype))
		{
			trigger_error('String expected for $vartype in _rex_cast_var()!', E_USER_ERROR);
			exit();
		}

		// Variable Casten
		switch($vartype)
		{
			case 'bool'   :
			case 'boolean': $var = (boolean) $var; break;
			case 'int'    :
			case 'integer': $var = (int)     $var; break;
			case 'double' : $var = (double)  $var; break;
			case 'float'  : $var = (float)   $var; break;
			case 'string' : $var = (string)  $var; break;
			case 'object' : $var = (object)  $var; break;
			case 'array'  : $var = (array)   $var; break;

			// kein Cast, nichts tun
			case ''       : break;

			// Evtl Typo im vartype, deshalb hier fehlermeldung!
			default: trigger_error('Unexpected vartype "'. $vartype .'" in _rex_cast_var()!', E_USER_ERROR); exit();
		}

		return $var;
	}
} // end function_exists

/**
 * für REDAXO 3.2.x
 */
if (!function_exists('rex_put_file_contents'))
{
	function rex_put_file_contents($path, $content)
	{
		global $REX;

		$writtenBytes = file_put_contents($path, $content);
		@chmod($path, $REX['FILEPERM']);

		return $writtenBytes;
	}
} // end function_exists

/**
 * für REDAXO 3.2.x
 */
if (!function_exists('rex_get_file_contents'))
{
	function rex_get_file_contents($path)
	{
		return file_get_contents($path);
	}
} // end function_exists

/**
 * für REDAXO 3.2.x
 */
if (!function_exists('rex_replace_dynamic_contents'))
{
	function rex_replace_dynamic_contents($path, $content)
	{
		if($fcontent = rex_get_file_contents($path))
		{
			$content = "// --- DYN\n". trim($content) ."\n// --- /DYN";
			$fcontent = ereg_replace("(\/\/.---.DYN.*\/\/.---.\/DYN)", $content, $fcontent);
			return rex_put_file_contents($path, $fcontent);
		}
		return false;
	}
} // End function_exists

/**
 * für REDAXO 4.0.x
 */
if (!function_exists('rex_highlight_string'))
{
	function rex_highlight_string($string, $return = false)
	{
	  $s = '<p class="rex-code">'. highlight_string($string, true) .'</p>';
	  if($return)
	  {
		return $s;
	  }
	  echo $s;  
	}
} // End function_exists

/**
 * für REDAXO 4.1.x
 */
if (!function_exists('rex_copyDir'))
{
	function rex_copyDir($srcdir, $dstdir, $startdir = "")
	{
	  global $REX;
	  
	  $debug = FALSE;
	  $state = TRUE;
	  
	  if(!is_dir($dstdir))
	  {
		$dir = '';
		foreach(explode(DIRECTORY_SEPARATOR, $dstdir) as $dirPart)
		{
		  $dir .= $dirPart . DIRECTORY_SEPARATOR;
		  if(strpos($startdir,$dir) !== 0 && !is_dir($dir))
		  {
			if($debug)
			  echo "Create dir '$dir'<br />\n";
			  
			mkdir($dir);
			chmod($dir, $REX['DIRPERM']);
		  }
		}
	  }
	  
	  if($curdir = opendir($srcdir))
	  {
		while($file = readdir($curdir))
		{
		  if($file != '.' && $file != '..' && $file != '.svn')
		  {
			$srcfile = $srcdir . DIRECTORY_SEPARATOR . $file;    
			$dstfile = $dstdir . DIRECTORY_SEPARATOR . $file;    
			if(is_file($srcfile))
			{
			  $isNewer = TRUE;
			  if(is_file($dstfile))
			  {
				$isNewer = (filemtime($srcfile) - filemtime($dstfile)) > 0;
			  }
				
			  if($isNewer)
			  {
				if($debug)
				  echo "Copying '$srcfile' to '$dstfile'...";
				if(copy($srcfile, $dstfile))
				{
				  touch($dstfile, filemtime($srcfile));
				  chmod($dstfile, $REX['FILEPERM']);
				  if($debug)
					echo "OK<br />\n";
				}
				else
				{
				  if($debug)
				   echo "Error: File '$srcfile' could not be copied!<br />\n";
				  return FALSE;
				}
			  }
			}
			else if(is_dir($srcfile))
			{
			  $state = rex_copyDir($srcfile, $dstfile, $startdir) && $state;
			}
		  }
		}
		closedir($curdir);
	  }
	  return $state;
	}
} // End function_exists

/**
 * für REDAXO 3.2.x
 */
if (!function_exists('rex_deleteDir'))
{
	function rex_deleteDir($file, $delete_folders = FALSE)
	{
	  $debug = FALSE;
	  $state = TRUE;
	  
	  $file = rtrim($file, DIRECTORY_SEPARATOR);

	  if (file_exists($file))
	  {
		// Fehler unterdrücken, falls keine Berechtigung
		if (@ is_dir($file))
		{
		  $handle = opendir($file);
		  if (!$handle)
		  {
			if($debug)
			  echo "Unable to open dir '$file'<br />\n";
			  
			return FALSE;
		  }

		  while ($filename = readdir($handle))
		  {
			if ($filename == '.' || $filename == '..')
			{
			  continue;
			}

			if (!rex_deleteDir($file.DIRECTORY_SEPARATOR.$filename, $delete_folders))
			{
			  $state = FALSE;
			}
		  }
		  closedir($handle);

		  if ($state !== TRUE)
		  {
			return FALSE;
		  }
		  

		  // Ordner auch löschen?
		  if ($delete_folders)
		  {
			// Fehler unterdrücken, falls keine Berechtigung
			if (!@ rmdir($file))
			{
			  if($debug)
				echo "Unable to delete folder '$file'<br />\n";
				
			  return FALSE;
			}
		  }
		}
		else
		{
		  // Datei löschen
		  // Fehler unterdrücken, falls keine Berechtigung
		  if (!@ unlink($file))
		  {
			if($debug)
			  echo "Unable to delete file '$file'<br />\n";
				
			return FALSE;
		  }
		}
	  }
	  else
	  {
		if($debug)
		  echo "file '$file'not found!<br />\n";
		// Datei/Ordner existiert nicht
		return FALSE;
	  }

	  return TRUE;
	}
} // end function_exists

/**
 * Workaround für PHP4
 */
if (!function_exists('file_get_contents'))
{
	function file_get_contents($filename)
	{
		$fp = fopen($filename, 'r');
		if ($fp)
		{
			$cont = fread($fp, filesize($filename));
			fclose($fp);
			return $cont;
		}
		
		return false;
	}
} // end function_exists

/**
 * Workaround für PHP4
 */
if (!function_exists('file_put_contents'))
{
	function file_put_contents($path, $content)
	{
		$fp = @fopen($path, 'wb');
		if ($fp)
		{
			fwrite($fp, $content);
			fclose($fp);
			return true;
		}
		return false;
	}
} // end function_exists
