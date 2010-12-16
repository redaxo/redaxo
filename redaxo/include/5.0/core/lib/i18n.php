<?php

/**
 * Sprachobjekt zur Internationalisierung (I18N)
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

class i18n
{
  private static
    $locales = array();
  private 
    $searchpath,
    $locale,
    $text,
  	$text_loaded;

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
    $this->text_loaded = FALSE;
  }

  /*
   * Lädt alle Übersetzungen der aktuellen Sprache aus dem Sprachpfad und fügt diese dem Katalog hinzu.
   */
  public function loadTexts()
  {
    if($this->appendFile($this->searchpath))
    {
  		$this->text_loaded = TRUE;
    }
  }
  
  /**
   * Sucht im angegebenden Ordner nach eine Sprachdatei der aktuellen Sprache und fügt diese dem Sprachkatalog an
   *  
   * @param string $searchPath Pfad in dem die Sprachdatei gesucht werden soll
   */
  public function appendFile($searchPath)
  {
    $filename = $searchPath . DIRECTORY_SEPARATOR . $this->locale . ".lang";
    return $this->appendFileName($filename);
  }
  
  /**
   * Fuegt die angegebene Datei $filename diese dem Sprachkatalog an
   *  
   * @param string $filename Datei die hinzugefügt werden soll
   */
  public function appendFileName($filename)
  {
    if (is_readable($filename))
    {
      $handle = fopen($filename, "r");
      if($handle)
      {
        while (!feof($handle))
        {
          $buffer = fgets($handle, 4096);
          if (preg_match("/^([^\s]*)\s*=\s*(.*)$/", $buffer, $matches))
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
   * @param string $key Zu suchender Schlüssel
   */
  public function msg($key)
  {
  	global $REX;
  	
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

    $argNum = func_num_args();
    if($argNum > 1)
    {
      $args = func_get_args();
      for($i = 1; $i < $argNum; $i++)
      {
        // zero indexed
        $patterns[] = '/\{'. ($i-1) .'\}/';
        $replacements[] = $args[$i];
      }
    }

    return preg_replace($patterns, $replacements, $msg);
  }

  /**
   * Fügt dem Sprachkatalog unter dem gegebenen Schlüssel eine neue Übersetzung hinzu 
   *  
   * @param string $key Schlüssel unter dem die Übersetzung abgelegt wird
   * @param string $msg Übersetzter Text
   */
  public function addMsg($key, $msg)
  {
    $this->text[$key] = $msg;
  }

  /**
   * Prüft ob der Sprachkatalog zu dem gegebenen Schlüssel eine Übersetzung beinhaltet
   * 
   * @param string $key Zu suchender Schlüssel
   * @return boolean TRUE Wenn der Schlüssel gefunden wurde, sonst FALSE
   */
  public function hasMsg($key)
  {
  	return isset ($this->text[$key]);
  }

  /**
   * Durchsucht den Searchpath nach allen verfügbaren Sprachdateien und gibt diese zurück
   * 
   * @param string $searchpath Zu duruchsuchender Ordner
   * @return array Array von gefundenen Sprachen (locales)
   */
  static public function getLocales($searchpath)
  {
    if (empty (self::$locales) && is_readable($searchpath))
    {
      self::$locales = array ();

      $handle = opendir($searchpath);
      while ($file = readdir($handle))
      {
        if ($file != "." && $file != "..")
        {
          if (preg_match("/^(\w+)\.lang$/", $file, $matches))
          {
            self::$locales[] = $matches[1];
          }
        }
      }
      closedir($handle);
    }

    return self::$locales;
  }

}
