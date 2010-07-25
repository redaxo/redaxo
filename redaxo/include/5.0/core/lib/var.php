<?php


/**
 * Abtrackte Basisklasse für REX_VARS
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_var
{
  // --------------------------------- Actions

  /**
   * Actionmethode:
   * 
   * Füllt des sql-Objekt aus dem $REX_ACTION Array
   */
  /*public*/ function setACValues(& $sql, $REX_ACTION, $escape = false)
  {
    // nichts tun
  }

  /**
   * Actionmethode:
   * 
   * Füllt des $REX_ACTION Array aus den Input Formularwerten (Request Werten)
   * 
   * @return REX_ACTION Array
   */
  /*public*/ function getACRequestValues($REX_ACTION)
  {
    return $REX_ACTION;
  }

  /**
   * Actionmethode:
   * 
   * Füllt des $REX_ACTION Arrays aus der Datenbank (rex_sql)
   * 
   * @return REX_ACTION Array
   */
  /*public*/ function getACDatabaseValues($REX_ACTION, & $sql)
  {
    return $REX_ACTION;
  }

  /**
   * Actionmethode:
   * 
   * Ersetzt im String $content alle vorkommenden REX-Variablen mit den Werten aus dem REX_ACTION Array
   * 
   * @return String Der geparste String
   */
  /*public*/ function getACOutput($REX_ACTION, $content)
  {
    $sql = rex_sql::factory();
    $this->setACValues($sql, $REX_ACTION);
    return $this->getBEOutput($sql, $content);
  }

  // --------------------------------- Ouput

  /**
   * Ersetzt im String $content alle vorkommenden REX-Variablen mit den Werten aus dem sql Objekt.
   * Die Darstellung erfolgt dabei im Frontend.
   *
   * FE = Frontend
   */
  /*public*/ function getFEOutput(& $sql, $content)
  {
    return $this->getBEOutput($sql, $content);
  }
  
  /**
   * Ersetzt im String $content alle vorkommenden REX-Variablen mit den Werten aus dem sql Objekt.
   * Die Darstellung erfolgt dabei zur Modul-Eingabe im Backend.
   *
   * BE = Backend
   */
  /*public*/ function getBEOutput(& $sql, $content)
  {
    return $content;
  }

  /**
   * Ersetzt im String $content alle vorkommenden REX-Variablen mit den Werten aus dem sql Objekt.
   * Die Darstellung erfolgt dabei zur Modul-Ausgabe im Backend.
   * 
   * BE = Backend
   */
  /*public*/ function getBEInput(& $sql, $content)
  {
    return $this->getBEOutput($sql, $content);
  }

  /**
   * Ersetzt im String $content alle vorkommenden REX-Variablen mit den Werten aus dem sql Objekt.
   * Die Darstellung erfolgt dabei im Frontend.
   */
  /*public*/ function getTemplate($content)
  {
  	return $content;
  }

  /**
   * Wandelt PHP Code in Einfache Textausgaben um
   */
  /*protected*/ function stripPHP($content)
  {
    $content = str_replace('<?', '&lt;?', $content);
    $content = str_replace('?>', '?&gt;', $content);
    return $content;
  }

  /**
   * GetValue Wrapper, da hier immer auf die gleiche Tabelle gearbeitet wird und
   * mit MySQL 3.x mit Tabellenprefix angegeben werden muss, da der SQL gleichnamige
   * Spalten unterschiedlicher Tabellen enthält.
   */
  /*protected*/ function getValue(& $sql, $value)
  {
    global $REX;
    return $sql->getValue($REX['TABLE_PREFIX'] . 'article_slice.' . $value);
  }
  /**
   * setValue Wrapper, da hier immer auf die gleiche Tabelle gearbeitet wird und
   * mit MySQL 3.x mit Tabellenprefix angegeben werden muss, da der SQL gleichnamige
   * Spalten unterschiedlicher Tabellen enthält.
   */
  /*protected*/ function setValue(& $sql, $fieldname, $value, $escape = false)
  {
    global $REX;

    if($escape)
      return $sql->setValue($REX['TABLE_PREFIX'] . 'article_slice.' . $fieldname, addslashes($value));

    return $sql->setValue($REX['TABLE_PREFIX'] . 'article_slice.' . $fieldname, $value);
  }

  /**
   * Callback um nicht explizit gehandelte OutputParameter zu behandeln
   */
  /*protected*/ function handleDefaultParam($varname, $args, $name, $value)
  {
    switch($name)
    {
      case '0'       : $name = 'id';
    	case 'id'      :
    	case 'prefix'  :
      case 'suffix'  :
      case 'ifempty' :
      case 'instead' :
      case 'callback':
      // beliebige custom params zulassen
      default:
      $args[$name] = (string) $value;
    }
    return $args;
  }

  /**
   * Parameter aus args auf die Ausgabe eines Widgets anwenden
   */
  /*protected*/ function handleGlobalWidgetParams($varname, $args, $value)
  {
    return $value;
  }

  /**
   * Parameter aus args auf den Wert einer Variablen anwenden
   */
  /*protected*/ function handleGlobalVarParams($varname, $args, $value)
  {
    if(isset($args['callback']))
    {
      $args['subject'] = $value;
      return rex_call_func($args['callback'], $args);
    }

    $prefix = '';
    $suffix = '';

    if(isset($args['instead']) && $value != '')
      $value = $args['instead'];
    
    if(isset($args['ifempty']) && $value == '')
      $value = $args['ifempty'];
      
    if($value != '' && isset($args['prefix']))
      $prefix = $args['prefix'];

    if($value != '' && isset($args['suffix']))
      $suffix = $args['suffix'];

    return $prefix . $value . $suffix;
  }
  
  /**
   * Parameter aus args zur Laufzeit auf den Wert einer Variablen anwenden.
   * Wichtig für Variablen, die Variable ausgaben haben.
   */
  /*protected*/ function handleGlobalVarParamsSerialized($varname, $args, $value)
  {
    $varname = str_replace('"', '\"', $varname);
    $args = str_replace('"', '\"', serialize($args));
    return 'rex_var::handleGlobalVarParams("'. $varname .'", unserialize("'. $args .'"), '. $value .')';
  }

  /**
   * Findet die Parameter der Variable $varname innerhalb des Strings $content.
   */
  /*protected*/ function getVarParams($content, $varname)
  {
    $result = array ();

    $match = $this->matchVar($content, $varname);
    
    foreach ($match as $param_str)
    {
    	$args = array();
    	$params = $this->splitString($param_str);
    	foreach ($params as $name => $value)
    	{
        $args = $this->handleDefaultParam($varname, $args, $name, $value);
    	}
      
      $result[] = array (
        $param_str,
        $args
      );
    }
    
    return $result;
  }

  /**
   * Durchsucht den String $content nach Variablen mit dem Namen $varname.
   * Gibt die Parameter der Treffer (Text der Variable zwischen den []) als Array zurück.
   */
  /*protected*/ function matchVar($content, $varname)
  {
    $result = array ();

    if (preg_match_all('/' . preg_quote($varname, '/') . '\[([^\]]*)\]/ms', $content, $matches))
    {
      foreach ($matches[1] as $match)
      {
        $result[] = $match;
      }
    }
    
    return $result;
  }
  
  /**
   * Extrahiert das Argument $name aus dem Array $args.
   * Wenn das Argument nicht gefunden werden kann, wird $default zurueckgegeben.
   * 
   * @return String Wert des Arguments zu $name oder $default wenn nicht vorhanden
   */
  /*protected*/ function extractArg($name, $args, $default = null)
  {
  	$val = $default;
  	if(isset($args[$name]))
  	{
  		$val = $args[$name];
  		unset($args[$name]);
  	}
  	return array($val, $args);
  }

  /**
   * Trennt einen String an Leerzeichen auf.
   * Abschnitte die in "" oder '' stehen, werden als ganzes behandelt und
   * darin befindliche Leerzeichen nicht getrennt.
   */
  /*protected*/ function splitString($string)
  {
    return rex_split_string($string);
  }

  /**
   * Prueft ob es sich bei dem aktuellen event um ein ADD-Event handelt.
   * 
   * @return boolean True wenn es sich um ein ADD-Event handelt, sonst FALSE 
   */
  /*public static*/ function isAddEvent()
  {
    return rex_request('function', 'string') == 'add';
  }

  /**
   * Prueft ob es sich bei dem aktuellen event um ein EDIT-Event handelt.
   * 
   * @return boolean True wenn es sich um ein EDIT-Event handelt, sonst FALSE 
   */
  /*public static*/ function isEditEvent()
  {
    return rex_request('function', 'string') == 'edit';
  }

  /**
   * Prueft ob es sich bei dem aktuellen event um ein DELETE-Event handelt.
   * 
   * @return boolean True wenn es sich um ein DELETE-Event handelt, sonst FALSE 
   */
  /*public static*/ function isDeleteEvent()
  {
    return rex_request('function', 'string') == 'delete';
  }
}