<?php

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

  if (is_callable($function))
  {
    $func = $function;
  }
  elseif (is_string($function) && strlen($function) > 0)
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