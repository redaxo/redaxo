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
    global $REX;

    if(isset($_SESSION[$varname][$REX['INSTNAME']]))
    {
      return self::castVar($_SESSION[$varname][$REX['INSTNAME']], $vartype, $default, 'found');
    }

    if($default === '')
    {
      return self::castVar($default, $vartype, $default, 'default');
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
    global $REX;

    $_SESSION[$varname][$REX['INSTNAME']] = $value;
  }

  /**
   * Deletes a session variable
   *
   * @param string $varname Variable name
   */
  static public function unsetSession($varname)
  {
    global $REX;

    unset($_SESSION[$varname][$REX['INSTNAME']]);
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
      throw new rexException('Scalar expected for $needle in arrayKeyCast()!');
    }

    if(array_key_exists($needle, $haystack))
    {
      return self::castVar($haystack[$needle], $vartype, $default, 'found');
    }

    if($default === '')
    {
      return self::castVar($default, $vartype, $default, 'default');
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
   * Possible REDAXO types:
   *  - rex-article-id
   *  - rex-category-id
   *  - rex-clang-id
   *  - rex-template-id
   *  - rex-ctype-id
   *  - rex-slice-id
   *  - rex-module-id
   *  - rex-action-id
   *  - rex-media-id
   *  - rex-mediacategory-id
   *  - rex-user-id
   *
   * @param mixed $var Variable to cast
   * @param string $vartype Variable type
   * @param mixed $default Default value
   * @param string $mode Mode
   *
   * @return mixed Castet value
   */
  static private function castVar($var, $vartype, $default, $mode)
  {
    global $REX;

    if(!is_string($vartype))
    {
      throw new rexException('String expected for $vartype in castVar()!');
    }

    switch($vartype)
    {
      // ---------------- REDAXO types
      case 'rex-article-id':
        $var = (int) $var;
        if($mode == 'found')
        {
          if(!rex_ooArticle::isValid(rex_ooArticle::getArticleById($var)))
            $var = (int) $default;
        }
        break;
      case 'rex-category-id':
        $var = (int) $var;
        if($mode == 'found')
        {
          if(!rex_ooCategory::isValid(rex_ooCategory::getCategoryById($var)))
            $var = (int) $default;
        }
        break;
      case 'rex-clang-id':
        $var = (int) $var;
        if($mode == 'found')
        {
          if(empty($REX['CLANG'][$var]))
            $var = (int) $default;
        }
        break;
      case 'rex-template-id':
      case 'rex-ctype-id':
      case 'rex-slice-id':
      case 'rex-module-id':
      case 'rex-action-id':
      case 'rex-media-id':
      case 'rex-mediacategory-id':
      case 'rex-user-id':
        // erstmal keine weitere validierung
        $var = (int) $var;
        break;

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
                $var[$key] = self::castVar($value, $matches[1], '', 'found');
              } catch (rexException $e) {
                // Evtl Typo im vartype, mit urspr. typ als fehler melden
                throw new rexException('Unexpected vartype "'. $vartype .'" in castVar()!');
              }
            }
          }
        }
        else
        {
          // Evtl Typo im vartype, deshalb hier fehlermeldung!
          throw new rexException('Unexpected vartype "'. $vartype .'" in castVar()!');
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
}