<?php

/**
 * Template Objekt.
 * Zuständig für die Verarbeitung eines Templates
 *
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_template
{
  private $id;

  public function __construct($template_id = 0)
  {
    $this->setId($template_id);
  }

  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = (int) $id;
  }

  public function getFile()
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

  static public function getFilePath($template_id)
  {
    if($template_id<1) return FALSE;

    return self::getTemplatesDir() .'/' . $template_id . '.template';
  }

  static public function getTemplatesDir()
  {
    return rex_path::cache('templates');
  }

  public function getTemplate()
  {
  	$file = $this->getFile();
  	if(!$file) return FALSE;

  	return rex_file::get($file);
  }

  public function generate()
  {
    if($this->getId()<1) return FALSE;

    return rex_generateTemplate($this->getId());
  }

  public function deleteCache()
  {
  	if($this->id<1) return FALSE;

		$file = $this->getFilePath($this->getId());
		rex_file::delete($file);
    return true;
  }

  static public function hasModule($template_attributes,$ctype,$module_id)
	{
		$template_modules = rex_getAttributes('modules', $template_attributes, array ());
		if(!isset($template_modules[$ctype]['all']) || $template_modules[$ctype]['all'] == 1)
			return TRUE;

		if(in_array($module_id,$template_modules[$ctype]))
			return TRUE;

	  return FALSE;
	}
}