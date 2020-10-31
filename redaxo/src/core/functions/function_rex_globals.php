<?php

/**
 * Getter functions for the superglobals.
 *
 * @package redaxo5
 */

/**
 * Returns the variable $varname of $_GET and casts the value.
 *
 * @param string $varname Variable name
 * @param string $vartype Variable type
 * @param mixed  $default Default value
 *
 * @return mixed
 */
function rex_get($varname, $vartype = '', $default = '')
{
    return rex_request::get($varname, $vartype, $default);
}

/**
 * Returns the variable $varname of $_POST and casts the value.
 *
 * @param string $varname Variable name
 * @param string $vartype Variable type
 * @param mixed  $default Default value
 *
 * @return mixed
 */
function rex_post($varname, $vartype = '', $default = '')
{
    return rex_request::post($varname, $vartype, $default);
}

/**
 * Returns the variable $varname of $_REQUEST and casts the value.
 *
 * @param string $varname Variable name
 * @param string $vartype Variable type
 * @param mixed  $default Default value
 *
 * @return mixed
 */
function rex_request($varname, $vartype = '', $default = '')
{
    return rex_request::request($varname, $vartype, $default);
}

/**
 * Returns the variable $varname of $_SERVER and casts the value.
 *
 * @param string $varname Variable name
 * @param string $vartype Variable type
 * @param mixed  $default Default value
 *
 * @return mixed
 */
function rex_server($varname, $vartype = '', $default = '')
{
    return rex_request::server($varname, $vartype, $default);
}

/**
 * Returns the variable $varname of $_SESSION and casts the value.
 *
 * @param string $varname Variable name
 * @param string $vartype Variable type
 * @param mixed  $default Default value
 *
 * @throws rex_exception
 *
 * @return mixed
 */
function rex_session($varname, $vartype = '', $default = '')
{
    return rex_request::session($varname, $vartype, $default);
}

/**
 * Sets a session variable.
 *
 * @param string $varname Variable name
 * @param mixed  $value   Value
 *
 * @throws rex_exception
 */
function rex_set_session($varname, $value)
{
    rex_request::setSession($varname, $value);
}

/**
 * Deletes a session variable.
 *
 * @param string $varname Variable name
 *
 * @throws rex_exception
 */
function rex_unset_session($varname)
{
    rex_request::unsetSession($varname);
}

/**
 * Returns the variable $varname of $_COOKIE and casts the value.
 *
 * @param string $varname Variable name
 * @param string $vartype Variable type
 * @param mixed  $default Default value
 *
 * @return mixed
 */
function rex_cookie($varname, $vartype = '', $default = '')
{
    return rex_request::cookie($varname, $vartype, $default);
}

/**
 * Returns the variable $varname of $_FILES and casts the value.
 *
 * @param string $varname Variable name
 * @param string $vartype Variable type
 * @param mixed  $default Default value
 *
 * @return mixed
 */
function rex_files($varname, $vartype = '', $default = '')
{
    return rex_request::files($varname, $vartype, $default);
}

/**
 * Returns the variable $varname of $_ENV and casts the value.
 *
 * @param string $varname Variable name
 * @param string $vartype Variable type
 * @param mixed  $default Default value
 *
 * @return mixed
 */
function rex_env($varname, $vartype = '', $default = '')
{
    return rex_request::env($varname, $vartype, $default);
}

/**
 * Returns the HTTP method of the current request.
 *
 * @return string HTTP method in lowercase (head,get,post,put,delete)
 */
function rex_request_method()
{
    return rex_request::requestMethod();
}
