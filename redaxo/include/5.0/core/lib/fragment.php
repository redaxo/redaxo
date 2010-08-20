<?php

class rex_fragment
{
  private static $fragmentDirs = array();
  
  private $filename;
  private $vars;
  
  /**
   * Creates a fragment with the given variables.
   * 
   * @param array $params A array of key-value pairs to pass as local parameters
   */
  public function rex_fragment(array $vars = array())
  {
    $this->vars = $vars;
  }
  
  /**
   * Add a path to the fragment search path
   * 
   * @param string $path A path to a directory where fragments can be found
   */
  public static function addDirectory($path)
  {
    // add the new directory in front of the already know dirs,
    // so a later caller can override core settings/fragments
    array_unshift(self::$fragmentDirs[], $path);
  }
  
  /**
   * Set the variable $name to the given value.
   * 
   * @param $name string The name of the variable.
   * @param $value mixed The value for the variable
   * @param $escape Flag which indicates if the value should be escaped or not.
   */
  public function setVar($name, $value, $escape = true)
  {
    if(is_null($name))
    {
      throw new Exception(sprintf('Expecting $name to be not null!'));
    }
    
    if($escape)
    {
      $this->vars[$name] = $this->escape($value);
    }
    else
    {
      $this->vars[$name] = $value;
    }
  }

  /**
   * Escapes the value $val
   * 
   * @param mixed $val the value to escape
   */
  private function escape($val)
  {
    if(is_array($val))
    {
      foreach($val as $k => $v)
      {
        $val[$k] = $this->escape($v);
      }
      return $val;
    }
    else if(is_string($val))
    {
      return htmlspecialchars($val);
    }
    else if(is_scalar($val))
    {
      return $val;
    }
    else
    {
      throw new Exception(sprintf('Unexpected type for $val, "%s" given', gettype($val)));
    }
  }
  
  /**
   * Parses the variables of the fragment into the file $filename
   *  
   * @param string $filename the filename of the fragment to parse.
   */
  public function parse($filename)
  {
    global $REX;
    
    if(!is_string($filename))
    {
      throw new Exception(sprintf('Expecting $filename to be a string, %s given!', gettype($filename)));
    }
    
    $this->filename = $filename;
    
    foreach(self::$fragmentDirs as $fragDir)
    {
      $fragment = $fragDir . $filename . '.tpl';
      if(is_readable($fragment))
      {
        ob_start();
        require $fragment;
        return ob_get_clean();
      }
    }
    
    throw new Exception(sprintf('Fragmentfile "%s" not found!', $template));
  }
  
  // -------------------------- in fragment helpers

  /**
   * Include a Subfragment from within a fragment.
   * 
   * The Subfragment gets all variables of the current fragment, plus optional overrides from $params
   * 
   * @param string $filename The filename of the fragment to use
   * @param array $params A array of key-value pairs to pass as local parameters
   */
  protected function subfragment($filename, array $params = array())
  {
    $fragment = new rex_fragment(array_merge($this->vars, $params), $this->basePath);
    echo $fragment->parse($filename);
  }
  
  /**
   * Translate the given key $key.
   * 
   * @param string $key The key to translate 
   */
  protected function i18n($key)
  {
    global $I18N;
    
    if(!is_string($key))
    {
      throw new Exception(sprintf('Expecting $key to be a string, %s given!', gettype($key)));
    }
    
    return $I18N->msg($key);
  }
  
  /**
   * Returns the config for key $key.
   * Enter description here ...
   * @param $key
   */
  protected function config($key)
  {
    global $REX;
    
    if(!is_string($key))
    {
      throw new Exception(sprintf('Expecting $key to be a string, %s given!', gettype($key)));
    }
    
    if(isset($REX[$key]))
    {
      return $REX[$key];
    }
    
    return null;
  }
  
  /**
   * Generates a url with the given parameters
   */
  protected function url(array $params = array())
  {
    if(!is_array($params))
    {
      throw new Exception(sprintf('Expecting $params to be a array, %s given!', gettype($filename)));
    }
    
    if(!isset($params['page']))
    {
      $page = rex_request('page');
      if($page != null)
      {
        $params['page'] = $page;
      }
    }
    if(!isset($params['subpage']))
    {
      $subpage = rex_request('subpage');
      if($subpage != null)
      {
        $params['subpage'] = $subpage;
      }
    }
    
    $url = 'index.php?';
    foreach($params as $key => $value)
    {
      $url .= $key .'='. urlencode($value) .'&';
    }
    return substr($url, 0, -1);
  }
  
  
  /**
   * Magic getter to reference variables from within the fragment.
   * 
   * @param string $name The name of the variable to get.
   */
  public function __get($name)
  {
    if(isset($this->vars[$name]))
    {
      return $this->vars[$name];
    }
    
    trigger_error(sprintf('Undefined variable "%s" in rex_fragment "%s"', $name, $this->filename), E_USER_WARNING);
    
    return null;
  }
  
  // /-------------------------- in fragment helpers
}