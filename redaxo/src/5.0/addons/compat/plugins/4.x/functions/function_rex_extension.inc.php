<?php

/**
 * @see rex_extension::registerPoint()
 *
 * @deprecated 5.0
 */
function rex_register_extension_point($extensionPoint, $subject = '', $params = array (), $read_only = false)
{
  return rex_extension::registerPoint($extensionPoint, $subject, $params, $read_only);
}

/**
 * @see rex_extension::register()
 *
 * @deprecated 5.0
 */
function rex_register_extensions($extensionPoint, $callable, $params = array())
{
  rex_extension::register($extensionPoint, $callable, $params);
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
 * @see rex_extension::getRegisteredExtensions()
 *
 * @deprecated 5.0
 */
function rex_get_registered_extensions($extensionPoint)
{
  return rex_extension::getRegisteredExtensions($extensionPoint);
}