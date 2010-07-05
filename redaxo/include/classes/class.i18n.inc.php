<?php

/**
 * Sprachobjekt zur Internationalisierung (I18N)
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

class i18n
{
  var $locales;
  var $searchpath;
  var $locale;
  var $text;
	var $text_loaded;

  /*
   * Constructor
   * the locale must of the common form, eg. de_de, en_us or just plain en, de.
   * the searchpath is where the language files are located
   */
  function i18n($locale = "de_de", $searchpath)
  {
    $this->searchpath = $searchpath;
    $this->text = array ();
    $this->locale = $locale;
    $this->locales = array ();
    $this->text_loaded = FALSE;
  }

  /*
   * Lädt alle Übersetzungen der aktuellen Sprache aus dem Sprachpfad und fügt diese dem Katalog hinzu.
   */
  function loadTexts()
  {
    if($this->appendFile($this->searchpath))
    {
  		$this->text_loaded = TRUE;
    }
  }
  
  /**
   * Sucht im angegebenden Ordner nach eine Sprachdatei der aktuellen Sprache und fügt diese dem Sprachkatalog an
   *  
   * @param $searchPath Pfad in dem die Sprachdatei gesucht werden soll
   */
  function appendFile($searchPath)
  {
    $filename = $searchPath . DIRECTORY_SEPARATOR . $this->locale . ".lang";
    return $this->appendFileName($filename);
  }
  
  /**
   * Fuegt die angegebene Datei $filename diese dem Sprachkatalog an
   *  
   * @param $filename Datei die hinzugefügt werden soll
   */
  function appendFileName($filename)
  {
    if (is_readable($filename))
    {
      $handle = fopen($filename, "r");
      if($handle)
      {
        while (!feof($handle))
        {
          $buffer = fgets($handle, 4096);
          if (preg_match("/^(\w*)\s*=\s*(.*)$/", $buffer, $matches))
          {
            $this->addMsg($matches[1], trim($matches[2]));
          }
        }
        fclose($handle);
        return TRUE;
      }
    }
    
    return FALSE;
  }

  /**
   * Durchsucht den Sprachkatalog nach einem Schlüssel und gibt die dazugehörige Übersetzung zurück
   * 
   * @param $key Zu suchender Schlüssel
   */
  function msg($key)
  {
  	global $REX;
  	
  	/*
  	// Warum hier umschalten der Sprache!?
  	if(isset($REX['LOGIN']) && is_object($REX['LOGIN']) && 
  	   $REX['LOGIN']->getLanguage() != $this->locale)
  	{
  		$this->locale = $REX['LOGIN']->getLanguage();
  		$this->text_loaded = FALSE;
  	}
  	*/
  	
  	if(!$this->text_loaded)
  	{
  	  $this->loadTexts();
  	}
  	
    if ($this->hasMsg($key))
    {
      $msg = $this->text[$key];
    }
    else
    {
      $msg = "[translate:$key]";
    }

    $patterns = array ();
    $replacements = array ();

    $args = func_get_args();
    for($i = 1; $i < func_num_args(); $i++)
    {
      // zero indexed
      $patterns[] = '/\{'. ($i-1) .'\}/';
      $replacements[] = $args[$i];
    }

    return preg_replace($patterns, $replacements, $msg);
  }

  /**
   * Fügt dem Sprachkatalog unter dem gegebenen Schlüssel eine neue Übersetzung hinzu 
   *  
   * @param $key Schlüssel unter dem die Übersetzung abgelegt wird
   * @param $msg Übersetzter Text
   */
  function addMsg($key, $msg)
  {
    $this->text[$key] = $msg;
  }

  /**
   * Prüft ob der Sprachkatalog zu dem gegebenen Schlüssel eine Übersetzung beinhaltet
   * 
   * @param $key Zu suchender Schlüssel
   * @return boolean TRUE Wenn der Schlüssel gefunden wurde, sonst FALSE
   */
  function hasMsg($key)
  {
  	return isset ($this->text[$key]);
  }

  /**
   * Durchsucht den Searchpath nach allen verfügbaren Sprachdateien und gibt diese zurück
   * 
   * @param $searchpath Zu duruchsuchender Ordner
   * @return array Array von gefundenen Sprachen (locales)
   */
  function getLocales($searchpath)
  {
    if (empty ($this->locales) && is_readable($searchpath))
    {
      $this->locales = array ();

      $handle = opendir($searchpath);
      while ($file = readdir($handle))
      {
        if ($file != "." && $file != "..")
        {
          if (preg_match("/^(\w+)\.lang$/", $file, $matches))
          {
            $this->locales[] = $matches[1];
          }
        }
      }
      closedir($handle);

    }

    return $this->locales;
  }

}

/**
 * Funktion zum Anlegen eines Sprache-Objekts
 * 
 * @param $locale Locale der Sprache
 * @param $searchpath Pfad zum Ordner indem die Sprachdatei gesucht werden soll
 * @param $setlocale TRUE, wenn die locale für die Umgebung gesetzt werden soll, sonst FALSE
 * @return unknown_type
 */
function rex_create_lang($locale = "de_de", $searchpath = '', $setlocale = TRUE)
{
  global $REX;

  $_searchpath = $searchpath;

  if ($searchpath == '')
  {
    $searchpath = $REX['INCLUDE_PATH'] .DIRECTORY_SEPARATOR. "lang";
  }
  $lang_object = new i18n($locale, $searchpath);

  if ($_searchpath == '')
  {
    $REX['LOCALES'] = $lang_object->getLocales($searchpath);
  }

  if($setlocale)
  {
    $locales = array();
    foreach(explode(',', trim($lang_object->msg('setlocale'))) as $locale)
    {
      $locales[]= $locale .'.'. strtoupper(str_replace('iso-', 'iso', $lang_object->msg('htmlcharset')));
      $locales[]= $locale .'.'. strtoupper(str_replace('iso-', 'iso', str_replace("-","",$lang_object->msg('htmlcharset'))));
      $locales[]= $locale .'.'. strtolower(str_replace('iso-', 'iso', $lang_object->msg('htmlcharset')));
      $locales[]= $locale .'.'. strtolower(str_replace('iso-', 'iso', str_replace("-","",$lang_object->msg('htmlcharset'))));
    }
    
    foreach(explode(',', trim($lang_object->msg('setlocale'))) as $locale)
      $locales[]= $locale;
    
    setlocale(LC_ALL, $locales);
  }

  return $lang_object;
}