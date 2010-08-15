<?php

class rex_fragment
{
  private $vars;
  
  public function rex_fragment()
  {
    $this->vars = array();
  }
  
  public function set($name, $value, $escape = true)
  {
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
    
    // TODO: allow override of template files
    $fragment = $REX["SRC_PATH"].'/core/fragments/'. $filename . '.tpl';
    
    if(file_exists($fragment))
    {
      require $fragment;
    }
    else 
    {
      throw new Exception(sprintf('Fragmentfile "%s" not found!', $template));
    }
  }

//  /**
//   * Include a fragment from within a fragment.
//   * @param string $template
//   */
//  protected function load($filename)
//  {
//    $this->parse($filename);
//  }
  
  protected function i18n($key)
  {
    global $I18N;
    return $I18N->msg($key);
  }
  
  public function __get($name)
  {
    if(isset($this->vars[$name]))
    {
      return $this->vars[$name];
    }
    return null;
  }
}