<?php

/**
 * Class for var casting
 *
 * @author gharlan
 */
class rex_type
{
  /**
  * Casts the variable $var to $vartype
  *
  * Possible types:
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
  *
  * @return mixed Castet value
  */
  static public function cast($var, $vartype)
  {
    if (!is_string($vartype))
    {
      throw new rex_exception('String expected for $vartype in cast()!');
    }

    switch ($vartype)
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
        if (empty($var))
          $var = array();
        else
          $var = (array) $var;
        break;

        // kein Cast, nichts tun
      case ''       : break;

      default:
        // check for array with generic type
        if (strpos($vartype, 'array[') === 0)
        {
          if (empty($var))
            $var = array();
          else
            $var = (array) $var;

          // check if every element in the array is from the generic type
          $matches = array();
          if (preg_match('@array\[([^\]]*)\]@', $vartype, $matches))
          {
            foreach ($var as $key => $value)
            {
              try
              {
                $var[$key] = self::cast($value, $matches[1], '');
              }
              catch (rex_exception $e)
              {
                // Evtl Typo im vartype, mit urspr. typ als fehler melden
                throw new rex_exception('Unexpected vartype "' . $vartype . '" in cast()!');
              }
            }
          }
        }
        else
        {
          // Evtl Typo im vartype, deshalb hier fehlermeldung!
          throw new rex_exception('Unexpected vartype "' . $vartype . '" in cast()!');
        }
    }

    return $var;
  }
}
