<?php

/**
 * Generic interface for classes which provide urls.
 * 
 * @author staabm
 */
interface rex_url_provider
{
  /**
   * Returns a Url which contains the given parameters.
   *
   * @param array $params A array containing key value pairs for the parameter and its value 
   *
   * @return string The generated Url
   */
  function getUrl(array $params = array());
}

/**
 * 
 * Enter description here ...
 * @author staabm
 */
class rex_url_provider_impl implements rex_url_provider
{
  private $globalParams;
  
  public function __construct(array $globalParams = array())
  {
    $this->globalParams = $globalParams;
  }
  
  private function array2paramStr(array $array)
  {
    $paramString = '';
    foreach($array as $name => $value)
    {
      if(is_array($value))
      {
        $paramString .= $this->array2paramStr($value);
      }
      else
      {
        $paramString .= '&'. $name .'='. $value;
      }
    }
    
    return $paramString;
  }
  
  public function getUrl(array $params = array())
  {
    global $REX;
    
    // combine global params with local
    $_params = array_merge($this->globalParams, $params);
    
    return str_replace('&', '&amp;', 'index.php?' .ltrim($this->array2paramStr($_params), '&'));
  }  
}