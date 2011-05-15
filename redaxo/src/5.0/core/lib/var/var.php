<?php

/**
 * Abstract baseclass for REX_VARS
 * @package redaxo5
 * @version svn:$Id$
 */
abstract class rex_var
{
  static private
    $vars = array(),
    $initialized = false;

  /**
   * Registers a REX_VAR
   *
   * @param string $var Class name of the REX_VAR
   */
  static public function registerVar($var)
  {
    if(!is_subclass_of($var, __CLASS__))
    {
      throw new rexException('$var must be a subclass of '. __CLASS__);
    }

    if(self::$initialized && !is_object($var))
    {
      self::$vars[] = new $var;
    }
    else
    {
      self::$vars[] = $var;
    }
  }

  /**
   * Returns the registered REX_VARS
   */
  static public function getVars()
  {
    if (!self::$initialized)
    {
      foreach(self::$vars as $key => $var)
      {
        if(!is_object($var))
          self::$vars[$key] = new $var;
      }
    }

    return self::$vars;
  }

  // --------------------------------- Actions

  /**
   * Actionmethod:
   *
   * Fill the rex_sql object with userinput from REX_ACTION
   *
   * @param rex_sql $sql A datacontainer of the current slice (to be filled)
   * @param array $REX_ACTION Array of userinput
   */
  public function setACValues(rex_sql $sql, array $REX_ACTION)
  {
    // nothing todo
  }

  /**
   * Actionmethod:
   *
   * Fill the REX_ACTION array with values out of the superglobal request vars
   *
   * @param array $REX_ACTION The array to fill
   *
   * @return array The filled REX_ACTION array
   */
  public function getACRequestValues(array $REX_ACTION)
  {
    return $REX_ACTION;
  }

  /**
   * Actionmethod:
   *
   * Fill the REX_ACTION array with initial values from the datacontainer
   *
   * @param array $REX_ACTION The array to fill
   * @param rex_sql $sql The datacontainer with database values
   *
   * @return array The filled REX_ACTION array
   */
  public function getACDatabaseValues(array $REX_ACTION, rex_sql $sql)
  {
    return $REX_ACTION;
  }

  /**
   * Actionmethod:
   *
   * Replaces all occurences of the REX-Var with values given in the REX_ACTION array
   *
   * @param array $REX_ACTION The array of slice-data
   * @param string $content The string for searching.
   *
   * @return string The string in which all occurences have been replaced
   */
  public function getACOutput(array $REX_ACTION, $content)
  {
    $sql = rex_sql::factory();
    $this->setACValues($sql, $REX_ACTION);
    return $this->getBEOutput($sql, $content);
  }

  // --------------------------------- Ouput

  /**
   * Replaces all occurences of the REX-Var with values given in the rex_sql container.
   * The content need to be prepared for <b>output</b> in the <b>frontend</b>.
   *
   * @param rex_sql $sql The datacontainer with database values
   * @param string $content The string for searching.
   *
   * @return string The string in which all occurences have been replaced
   */
  public function getFEOutput(rex_sql $sql, $content)
  {
    return $this->getBEOutput($sql, $content);
  }

  /**
   * Replaces all occurences of the REX-Var with values given in the rex_sql container.
   * The content need to be prepared for <b>output</b> in the <b>backend</b>.
   *
   * @param rex_sql $sql The datacontainer with database values
   * @param string $content The string for searching.
   *
   * @return string The string in which all occurences have been replaced
   */
  public function getBEOutput(rex_sql $sql, $content)
  {
    return $content;
  }

  /**
   * Replaces all occurences of the REX-Var with values given in the rex_sql container.
   * The content need to be prepared for <b>input</b> in the <b>backend</b>.
   *
   * @param rex_sql $sql The datacontainer with database values
   * @param string $content The string for searching.
   *
   * @return string The string in which all occurences have been replaced
   */
  public function getBEInput(rex_sql $sql, $content)
  {
    return $this->getBEOutput($sql, $content);
  }

  /**
   * Replaces all occurences of the REX-Var in the given Template string.
   * The content need to be prepared for <b>output</b> in the <b>frontend</b>.
   *
   * @param string $content The string for searching.
   *
   * @return string The string in which all occurences have been replaced
   */
  public function getTemplate($content)
  {
  	return $content;
  }

  /**
   * Escapes php-tags in the given content string
   *
   * @param string $content The string to escape
   *
   * @return string The escaped string
   */
  protected function stripPHP($content)
  {
    $content = str_replace('<?', '&lt;?', $content);
    $content = str_replace('?>', '?&gt;', $content);
    return $content;
  }

  /**
   * Gets the article-slice property which name equals to $value.
   *
   * @param rex_sql $sql The slice datacontainer
   * @param string $value The name of the property to search for
   *
   * @return string The value of the property, or <code>false</code> when the property cannot be found!
   */
  protected function getValue(rex_sql $sql, $value)
  {
    return $sql->getValue(rex_core::getTablePrefix() . 'article_slice.' . $value);
  }

  /**
   * Sets the article-slice property $fieldname with the given $value.
   *
   * @param rex_sql $sql The article-slice datacontainer
   * @param string $fieldname The name of the property to set
   * @param string $value The value to set
   */
  protected function setValue(rex_sql $sql, $fieldname, $value)
  {
    $sql->setValue($fieldname, $value);
  }

  /**
   * Handle all common REX-Var parameters.
   * The parameter $name will be extracted out of $args and set to $value.
   *
   * @param string $varname The name of the variable which param should be handled
   * @param array $args The array of parameters which are already known for the variable $varname
   * @param string $name The name of the parameter to extract
   * @param string $value The value to set for the parameter
   *
   * @return array The adjusted array of parameters
   */
  static public function handleDefaultParam($varname, array $args, $name, $value)
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
   * Handle all common widget parameters.
   *
   * @param string $varname The name of the variable which param should be handled
   * @param array $args The array of parameters for the widget
   * @param string $widgetSource The html source of the widget
   *
   * @return string The parsed html source
   */
  static public function handleGlobalWidgetParams($varname, array $args, $widgetSource)
  {
    return $widgetSource;
  }

  /**
   * Handle all common var parameters.
   *
   * @param string $varname The name of the variable which param should be handled
   * @param array $args The array of parameters for the widget
   * @param string $value The value of the variable
   *
   * @return string The parsed variable value
   */
  static public function handleGlobalVarParams($varname, array $args, $value)
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
   * Handle all common var parameters at runtime.
   * This method returns the php-code which handles the variable values.
   *
   * @param string $varname The name of the variable which param should be handled
   * @param array $args The array of parameters for the widget
   * @param string $value The value of the variable
   *
   * @return string The code which parses the variable value
   */
  static public function handleGlobalVarParamsSerialized($varname, array $args, $value)
  {
    $varname = str_replace("'", "\'", $varname);
    $json = str_replace('"', '\"', json_encode($args));
    //  use double-quotes inside json_decode so php-vars in the json string get evaluated by the interpreter
    return 'rex_var::handleGlobalVarParams(\''. $varname .'\', json_decode("'. $json .'", true), '. $value .')';
  }

  /**
   * Search all occurences of the parameter $varname in $content and returns it parsed parameters.
   * The origin parameter-string and all parsed default parameters are contained per hit in the resulting array.
   *
	 * @param string $content The string for searching
   * @param string $varname The name of the variable
   *
   * @return array A array containg all parameter-matches of the variable $varname in $content
   */
  protected function getVarParams($content, $varname)
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

    	// the origin param_str is needed to str_replace the variable at parse-time
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

  /**
   * Search all occurences of the variable $varname in $content and
   * returns the corresponding parameter string of each match.
   *
	 * @param string $content The string for searching
   * @param string $varname The name of the variable
   *
   * @return array A array containg all matches of the variable $varname in $content
   */
  protected function matchVar($content, $varname)
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
   * Get the argument $name out of the array $args.
   *
   * If the value will not be found $default is returned.
   * The default value will also be written into the array $args.
   *
   * @param string $name
   * @param array $args
   * @param string $default
   *
   * @return string the value of the arg, or $default if the arg cannot be found
   */
  protected function getArg($name, array &$args, $default = null)
  {
  	if(isset($args[$name]))
  	{
  		return $args[$name];
  	}
  	// we write the default back into the array, to get the default also into the parameters for the later callback
  	$args[$name] = $default;
  	return $default;
  }

  /**
   * Split a string on every space which it contains.
   * Spaces within single or double quotes are preserved.
   *
   * @param string $string The string to be splitted
   *
   * @return array The splitted string in array form.
   */
  protected function splitString($string)
  {
    return rex_split_string($string);
  }

  /**
   * Checks whether the handled event is an ADD-Event.
   *
   * @return boolean TRUE when the event is an ADD-Event otherwise FALSE.
   */
  static public function isAddEvent()
  {
    return rex_request('function', 'string') == 'add';
  }

  /**
   * Checks whether the handled event is an EDIT-Event.
   *
   * @return boolean TRUE when the event is an EDIT-Event otherwise FALSE.
   */
  static public function isEditEvent()
  {
    return rex_request('function', 'string') == 'edit';
  }

  /**
   * Checks whether the handled event is an DELETE-Event.
   *
   * @return boolean TRUE when the event is an DELETE-Event otherwise FALSE.
   */
  static public function isDeleteEvent()
  {
    return rex_request('function', 'string') == 'delete';
  }
}