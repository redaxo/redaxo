<?php

define('REX_EXTENSION_EARLY', rex_extension::EARLY);
define('REX_EXTENSION_NORMAL', rex_extension::NORMAL);
define('REX_EXTENSION_LATE', rex_extension::LATE);

/**
 * @see rex_extension::registerPoint()
 *
 * @deprecated 5.0
 */
function rex_register_extension_point($extensionPoint, $subject = '', $params = array(), $read_only = false)
{
  return rex_extension::registerPoint($extensionPoint, $subject, $params, $read_only);
}

/**
 * @see rex_extension::register()
 *
 * @deprecated 5.0
 */
function rex_register_extension($extensionPoint, $callable, $params = array(), $level = REX_EXTENSION_NORMAL)
{
  rex_extension::register($extensionPoint, $callable, $level, $params);
}

/**
 * @see rex_extension::isRegistered()
 *
 * @deprecated 5.0
 */
function rex_extension_is_registered($extensionPoint)
{
  return rex_extension::isRegistered($extensionPoint);
}

/**
 * @link http://www.php.net/manual/en/function.call-user-func.php
 * @link http://www.php.net/manual/en/function.call-user-func-array.php
 *
 * @deprecated 5.0
 */
function rex_call_func($function, $params, $parseParamsAsArray = true)
{
  if ($parseParamsAsArray === true) {
    return call_user_func($function, $params);
  }
  return call_user_func_array($function, $params);
}

/**
 * @link http://www.php.net/manual/en/function.is-callable.php
 *
 * @deprecated 5.0
 */
function rex_check_callable($_callable)
{
  return is_callable($_callable);
}
