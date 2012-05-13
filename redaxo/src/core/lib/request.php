<?php

/**
 * Class for getting the superglobals
 */
class rex_request
{
  /**
   * Returns the variable $varname of $_GET and casts the value
   *
   * @param string $varname Variable name
   * @param string $vartype Variable type
   * @param mixed $default Default value
   *
   * @return mixed
   */
  static public function get($varname, $vartype = '', $default = '')
  {
    return self::arrayKeyCast($_GET, $varname, $vartype, $default);
  }

  /**
   * Returns the variable $varname of $_POST and casts the value
   *
   * @param string $varname Variable name
   * @param string $vartype Variable type
   * @param mixed $default Default value
   *
   * @return mixed
   */
  static public function post($varname, $vartype = '', $default = '')
  {
    return self::arrayKeyCast($_POST, $varname, $vartype, $default);
  }

  /**
   * Returns the variable $varname of $_REQUEST and casts the value
   *
   * @param string $varname Variable name
   * @param string $vartype Variable type
   * @param mixed $default Default value
   *
   * @return mixed
   */
  static public function request($varname, $vartype = '', $default = '')
  {
    return self::arrayKeyCast($_REQUEST, $varname, $vartype, $default);
  }

  /**
   * Returns the variable $varname of $_SERVER and casts the value
   *
   * @param string $varname Variable name
   * @param string $vartype Variable type
   * @param mixed $default Default value
   *
   * @return mixed
   */
  static public function server($varname, $vartype = '', $default = '')
  {
    return self::arrayKeyCast($_SERVER, $varname, $vartype, $default);
  }

  /**
   * Returns the variable $varname of $_SESSION and casts the value
   *
   * @param string $varname Variable name
   * @param string $vartype Variable type
   * @param mixed $default Default value
   *
   * @return mixed
   */
  static public function session($varname, $vartype = '', $default = '')
  {
    if(isset($_SESSION[$varname][rex::getProperty('instname')]))
    {
      return self::castVar($_SESSION[$varname][rex::getProperty('instname')], $vartype, $default);
    }

    if($default === '')
    {
      return self::castVar($default, $vartype, $default);
    }
    return $default;
  }

  /**
   * Sets a session variable
   *
   * @param string $varname Variable name
   * @param mixed $value Value
   */
  static public function setSession($varname, $value)
  {
    $_SESSION[$varname][rex::getProperty('instname')] = $value;
  }

  /**
   * Deletes a session variable
   *
   * @param string $varname Variable name
   */
  static public function unsetSession($varname)
  {
    unset($_SESSION[$varname][rex::getProperty('instname')]);
  }

  /**
   * Returns the variable $varname of $_COOKIE and casts the value
   *
   * @param string $varname Variable name
   * @param string $vartype Variable type
   * @param mixed $default Default value
   *
   * @return mixed
   */
  static public function cookie($varname, $vartype = '', $default = '')
  {
    return self::arrayKeyCast($_COOKIE, $varname, $vartype, $default);
  }

  /**
   * Returns the variable $varname of $_FILES and casts the value
   *
   * @param string $varname Variable name
   * @param string $vartype Variable type
   * @param mixed $default Default value
   *
   * @return mixed
   */
  static public function files($varname, $vartype = '', $default = '')
  {
    return self::arrayKeyCast($_FILES, $varname, $vartype, $default);
  }

  /**
   * Returns the variable $varname of $_ENV and casts the value
   *
   * @param string $varname Variable name
   * @param string $vartype Variable type
   * @param mixed $default Default value
   *
   * @return mixed
   */
  static public function env($varname, $vartype = '', $default = '')
  {
    return self::arrayKeyCast($_ENV, $varname, $vartype, $default);
  }

  /**
   * Searches the value $needle in array $haystack and returns the casted value
   *
   * @param array $haystack Array
   * @param scalar $needle Value to search
   * @param string $vartype Variable type
   * @param mixed $default Default value
   *
   * @return mixed
   */
  static private function arrayKeyCast(array $haystack, $needle, $vartype, $default = '')
  {
    if(!is_scalar($needle))
    {
      throw new rex_exception('Scalar expected for $needle in arrayKeyCast()!');
    }

    if(array_key_exists($needle, $haystack))
    {
      return self::castVar($haystack[$needle], $vartype, $default);
    }

    if($default === '')
    {
      return self::castVar($default, $vartype, $default);
    }
    return $default;
  }

  /**
   * Casts the variable $var to $vartype
   *
   * Possible PHP types:
   *  - bool (or boolean)
   *  - int (or integer)
   *  - double
   *  - string
   *  - float
   *  - real
   *  - object
   *  - array
   *  - array[<type>], e.g. array[int]
   *  - '' (don't cast)
   *
   * @param mixed $var Variable to cast
   * @param string $vartype Variable type
   * @param mixed $default Default value
   *
   * @return mixed Castet value
   */
  static private function castVar($var, $vartype, $default)
  {
    if(!is_string($vartype))
    {
      throw new rex_exception('String expected for $vartype in castVar()!');
    }

    switch($vartype)
    {
      // ---------------- PHP types
      case 'bool'   :
      case 'boolean':
        $var = (boolean) $var;
        break;
      case 'int'    :
      case 'integer':
        $var = (int)     $var;
        break;
      case 'double' :
        $var = (double)  $var;
        break;
      case 'float'  :
      case 'real'   :
        $var = (float)   $var;
        break;
      case 'string' :
        $var = (string)  $var;
        break;
      case 'object' :
        $var = (object)  $var;
        break;
      case 'array'  :
        if(empty($var))
          $var = array();
        else
          $var = (array) $var;
        break;

      // kein Cast, nichts tun
      case ''       : break;

      default:
        // check for array with generic type
        if(strpos($vartype, 'array[') === 0)
        {
          if(empty($var))
            $var = array();
          else
            $var = (array) $var;

          // check if every element in the array is from the generic type
          $matches = array();
          if(preg_match('@array\[([^\]]*)\]@', $vartype, $matches))
          {
            foreach($var as $key => $value)
            {
              try {
                $var[$key] = self::castVar($value, $matches[1], '');
              } catch (rex_exception $e) {
                // Evtl Typo im vartype, mit urspr. typ als fehler melden
                throw new rex_exception('Unexpected vartype "'. $vartype .'" in castVar()!');
              }
            }
          }
        }
        else
        {
          // Evtl Typo im vartype, deshalb hier fehlermeldung!
          throw new rex_exception('Unexpected vartype "'. $vartype .'" in castVar()!');
        }
    }

    return $var;
  }

  /**
   * Returns the HTTP method of the current request
   *
   * @return String HTTP method in lowercase (head,get,post,put,delete)
   */
  static public function requestMethod()
  {
    return isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';
  }

  /**
   * Returns true if the request is a XMLHttpRequest.
   *
   * This only works if your javaScript library sets an X-Requested-With HTTP header.
   * This is the case with Prototype, Mootools, jQuery, and perhaps others.
   *
   * Inspired by a method of the symfony framework.
   *
   * @return bool true if the request is an XMLHttpRequest, false otherwise
   */
  static public function isXmlHttpRequest()
  {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
  }

  /**
   * Returns true if the request is a PJAX-Request
   *
   * @see http://pjax.heroku.com/
   */
  static public function isPJAXRequest()
  {
    return isset($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] == 'true';
  }
}
