<?php

/**
 * Funktionen zur Registrierung von Schnittstellen (EXTENSION_POINTS)
 * @package redaxo4
 * @version svn:$Id$
 */

/**
 * Definiert einen Extension Point
 *
 * @param $extensionPoint Name des ExtensionPoints
 * @param $subject Objekt/Variable die beeinflusst werden soll
 * @param $params Parameter für die Callback-Funktion
 */
function rex_register_extension_point($extensionPoint, $subject = '', $params = array (), $read_only = false)
{
  global $REX;
  $result = $subject;

  if (!is_array($params))
  {
    $params = array ();
  }

  // Name des EP als Parameter mit übergeben
  $params['extension_point'] = $extensionPoint;

  if (isset ($REX['EXTENSIONS'][$extensionPoint]) && is_array($REX['EXTENSIONS'][$extensionPoint]))
  {
    $params['subject'] = $subject;
    if ($read_only)
    {
      foreach ($REX['EXTENSIONS'][$extensionPoint] as $ext)
      {
        $func = $ext[0];
        $local_params = array_merge($params, $ext[1]);
        rex_call_func($func, $local_params);
      }
    }
    else
    {
      foreach ($REX['EXTENSIONS'][$extensionPoint] as $ext)
      {
        $func = $ext[0];
        $local_params = array_merge($params, $ext[1]);
        $temp = rex_call_func($func, $local_params);
        // Rückgabewert nur auswerten wenn auch einer vorhanden ist
        // damit $params['subject'] nicht verfälscht wird
        // null ist default Rückgabewert, falls kein RETURN in einer Funktion ist
        if($temp !== null)
        {
          $result = $temp;
          $params['subject'] = $result;
        }
      }
    }
  }
  return $result;
}

/**
 * Definiert eine Callback-Funktion, die an dem Extension Point $extension aufgerufen wird
 *
 * @param $extension Name des ExtensionPoints
 * @param $function Name der Callback-Funktion
 * @param [$params] Array von zusätzlichen Parametern
 */
function rex_register_extension($extensionPoint, $callable, $params = array())
{
  global $REX;

  if(!is_array($params)) $params = array();
  $REX['EXTENSIONS'][$extensionPoint][] = array($callable, $params);
}

/**
 * Prüft ob eine extension für den angegebenen Extension Point definiert ist
 *
 * @param $extensionPoint Name des ExtensionPoints
 */
function rex_extension_is_registered($extensionPoint)
{
  global $REX;

  return !empty ($REX['EXTENSIONS'][$extensionPoint]);
}

/**
 * Gibt ein Array mit Namen von Extensions zurück, die am angegebenen Extension Point definiert wurden
 *
 * @param $extensionPoint Name des ExtensionPoints
 */
function rex_get_registered_extensions($extensionPoint)
{
  if(rex_extension_is_registered($extensionPoint))
  {
    global $REX;
    return $REX['EXTENSIONS'][$extensionPoint][0];
  }
  return array();
}

/**
 * Aufruf einer Funtion (Class-Member oder statische Funktion)
 *
 * @param $function Name der Callback-Funktion
 * @param $params Parameter für die Funktion
 *
 * @example
 *   rex_call_func( 'myFunction', array( 'Param1' => 'ab', 'Param2' => 12))
 * @example
 *   rex_call_func( 'myObject::myMethod', array( 'Param1' => 'ab', 'Param2' => 12))
 * @example
 *   rex_call_func( array('myObject', 'myMethod'), array( 'Param1' => 'ab', 'Param2' => 12))
 * @example
 *   $myObject = new myObject();
 *   rex_call_func( array($myObject, 'myMethod'), array( 'Param1' => 'ab', 'Param2' => 12))
 */
function rex_call_func($function, $params, $parseParamsAsArray = true)
{
  $func = '';

  if (is_string($function) && strlen($function) > 0)
  {
    // static class method
    if (strpos($function, '::') !== false)
    {
      $_match = explode('::', $function);
      $_class_name = trim($_match[0]);
      $_method_name = trim($_match[1]);

      rex_check_callable($func = array ($_class_name, $_method_name));
    }
    // function call
    elseif (function_exists($function))
    {
      $func = $function;
    }
    else
    {
      trigger_error('rexCallFunc: Function "'.$function.'" not found!', E_USER_ERROR);
    }
  }
  // object->method call
  elseif (is_array($function))
  {
    $_object = $function[0];
    $_method_name = $function[1];

    rex_check_callable($func = array ($_object, $_method_name));
  }
  else
  {
    trigger_error('rexCallFunc: Using of an unexpected function var "'.$function.'"!');
  }

	if($parseParamsAsArray === true)
	{
		// Alle Parameter als ein Array übergeben
		// funktion($params);
	  return call_user_func($func, $params);
	}
	// Jeder index im Array ist ein Parameter
	// funktion($params[0], $params[1], $params[2],...);
  return call_user_func_array($func, $params);
}

function rex_check_callable($_callable)
{
  if (is_callable($_callable))
  {
    return true;
  }
  else
  {
    if (!is_array($_callable))
    {
      trigger_error('rexCallFunc: Unexpected vartype for $_callable given! Expecting Array!', E_USER_ERROR);
    }
    $_object = $_callable[0];
    $_method_name = $_callable[1];

    if (!is_object($_object))
    {
      $_class_name = $_object;
      if (!class_exists($_class_name))
      {
        trigger_error('rexCallFunc: Class "'.$_class_name.'" not found!', E_USER_ERROR);
      }
    }
    trigger_error('rexCallFunc: No such method "'.$_method_name.'" in class "'.get_class($_object).'"!', E_USER_ERROR);
  }
}