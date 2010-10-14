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
   * @param array $params A array containing key value pairs for the parameter and its value.
   *
   * @return string The generated Url
   */
  function getUrl(array $params = array());
}

/**
 * Generic interface for classes which provide a complete rex-context.
 * A rex-context consists of a set of parameters which may get passed using urls (via parameter) or forms (via hidden input fields).
 * @author staabm
 */
interface rex_context_provider extends rex_url_provider
{
  /**
   * Returns a html string containg hidden input fields for the given parameters.
   *
   * @param array $params A array containing key value pairs for the parameter and its value. 
   *
   * @return string The generated html source containing the hidden input fields
   */
  function getHiddenInputFields(array $params = array());
}

/**
 * A generic implementiation of rex_context_provider
 * @author staabm
 */
class rex_context implements rex_context_provider
{
  private $globalParams;
  
  public function __construct(array $globalParams = array())
  {
    $this->globalParams = $globalParams;
  }
  
  public function getUrl(array $params = array())
  {
    // combine global params with local
    $_params = array_merge($this->globalParams, $params);
    
    return str_replace('&', '&amp;', 'index.php?' .ltrim(self::array2paramStr($_params), '&'));
  }
  
  public function getHiddenInputFields(array $params = array())
  {
    // combine global params with local
    $_params = array_merge($this->globalParams, $params);
    
    return self::array2inputStr($_params);
  }
  
  private function array2paramStr(array $array)
  {
    $paramString = '';
    foreach($array as $name => $value)
    {
      if(is_array($value))
      {
        $paramString .= self::array2paramStr($value);
      }
      else
      {
        $paramString .= '&'. urlencode($name) .'='. urlencode($value);
      }
    }
    
    return $paramString;
  }
  
  private static function array2inputStr(array $array)
  {
    $inputString = '';
    foreach($array as $name => $value)
    {
      if(is_array($value))
      {
        $inputString .= self::array2paramStr($value);
      }
      else
      {
        $inputString .= '<input type="hidden" name="'. $name .'" value="'. htmlspecialchars($value) .'" />';
      }
    }
    
    return $inputString;
  }
}