<?php

/**
 * Template Objekt.
 * Zuständig für die Verarbeitung eines Templates
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_template
{
  var $id;

  function rex_template($template_id = 0)
  {
    $this->setId($template_id);
  }

  /*public*/ function getId()
  {
    return $this->id;
  }

  /*public*/ function setId($id)
  {
    $this->id = (int) $id;
  }

  /*public*/ function getFile()
  {
    if($this->getId()<1) return FALSE;
    
  	$file = $this->getFilePath($this->getId());
  	if(!$file) return FALSE;
  	
  	if(!file_exists($file))
  	{
      // Generated Datei erzeugen
  		if(!$this->generate())
  		{
		    trigger_error('Unable to generate rexTemplate with id "'. $this->getId() . '"', E_USER_ERROR);
		
		    return FALSE;
  		}
  	}
  	
    return $file;
  }

  /*public*/ function getFilePath($template_id)
  {
    if($template_id<1) return FALSE;

    return rex_template::getTemplatesDir() .'/' . $template_id . '.template';
  }

  /*public*/ function getTemplatesDir()
  {
    global $REX;

    return $REX['INCLUDE_PATH'] . '/generated/templates';
  }

  /*public*/ function getTemplate()
  {
  	$file = $this->getFile();
  	if(!$file) return FALSE;
  	
  	return rex_get_file_contents($file);
  }

  /*public*/ function generate()
  {
    global $REX;

    if($this->getId()<1) return FALSE;

    include_once ($REX['INCLUDE_PATH'].'/functions/function_rex_generate.inc.php');
    return rex_generateTemplate($this->getId());
  }

  /*public*/ function deleteCache()
  {
  	global $REX;

		if($this->id<1) return FALSE;

		$file = $this->getFilePath($this->getId());
    return @unlink($file);
  }
  
  /*public static*/ function hasModule($template_attributes,$ctype,$module_id)
	{
		$template_modules = rex_getAttributes('modules', $template_attributes, array ());
		if(!isset($template_modules[$ctype]['all']) || $template_modules[$ctype]['all'] == 1)
			return TRUE;
		
		if(in_array($module_id,$template_modules[$ctype]))
			return TRUE;
		
	  return FALSE;
	}
}