<?php

class rex_fragment
{
  private $filename;
  private $vars;
  
  /**
   * Creates a fragment with the given variables.
   * 
   * @param array $params A array of key-value pairs to pass as local parameters
   */
  public function rex_fragment($vars = array())
  {
    $this->vars = $vars;
  }
  
  public function setVar($name, $value, $escape = true)
  {
    if(!is_string($name))
    {
      throw new Exception(sprintf('Expecting $name to be a string, %s given!', gettype($name)));
    }
    
    if($escape)
    {
      $this->vars[$name] = htmlspecialchars($value);
    }
    else
    {
      $this->vars[$name] = $value;
    }
  }
  
  public function parse($filename)
  {
    global $REX;
    
    if(!is_string($filename))
    {
      throw new Exception(sprintf('Expecting $filename to be a string, %s given!', gettype($filename)));
    }
    
    $this->filename = $filename;
    // TODO: allow override of template files
    $fragment = $REX["SRC_PATH"].'/core/fragments/'. $filename . '.tpl';
    
    if(file_exists($fragment))
    {
      ob_start();
      require $fragment;
      return ob_get_clean();
    }
    else 
    {
      throw new Exception(sprintf('Fragmentfile "%s" not found!', $template));
    }
  }

  /**
   * Include a Subfragment from within a fragment.
   * 
   * The Subfragment gets all variables of the current fragment, plus optional overrides from $params
   * 
   * @param string $filename The filename of the fragment to use
   * @param array $params A array of key-value pairs to pass as local parameters
   */
  protected function subfragment($filename, $params = array())
  {
    $fragment = new rex_fragment(array_merge($this->vars, $params));
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
    return $I18N->msg($key);
  }
  
  protected function config($key)
  {
    global $REX;
    
    if(isset($REX[$key]))
    {
      return $REX[$key];
    }
    return null;
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
}