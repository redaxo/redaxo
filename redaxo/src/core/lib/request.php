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
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public static function get($varname, $vartype = '', $default = '')
    {
        return self::arrayKeyCast($_GET, $varname, $vartype, $default);
    }

    /**
     * Returns the variable $varname of $_POST and casts the value
     *
     * @param string $varname Variable name
     * @param string $vartype Variable type
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public static function post($varname, $vartype = '', $default = '')
    {
        return self::arrayKeyCast($_POST, $varname, $vartype, $default);
    }

    /**
     * Returns the variable $varname of $_REQUEST and casts the value
     *
     * @param string $varname Variable name
     * @param string $vartype Variable type
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public static function request($varname, $vartype = '', $default = '')
    {
        return self::arrayKeyCast($_REQUEST, $varname, $vartype, $default);
    }

    /**
     * Returns the variable $varname of $_SERVER and casts the value
     *
     * @param string $varname Variable name
     * @param string $vartype Variable type
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public static function server($varname, $vartype = '', $default = '')
    {
        return self::arrayKeyCast($_SERVER, $varname, $vartype, $default);
    }

    /**
     * Returns the variable $varname of $_SESSION and casts the value
     *
     * @param string $varname Variable name
     * @param string $vartype Variable type
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public static function session($varname, $vartype = '', $default = '')
    {
        if (isset($_SESSION[$varname][rex::getProperty('instname')])) {
            return rex_type::cast($_SESSION[$varname][rex::getProperty('instname')], $vartype);
        }

        if ($default === '') {
            return rex_type::cast($default, $vartype);
        }
        return $default;
    }

    /**
     * Sets a session variable
     *
     * @param string $varname Variable name
     * @param mixed  $value   Value
     */
    public static function setSession($varname, $value)
    {
        $_SESSION[$varname][rex::getProperty('instname')] = $value;
    }

    /**
     * Deletes a session variable
     *
     * @param string $varname Variable name
     */
    public static function unsetSession($varname)
    {
        unset($_SESSION[$varname][rex::getProperty('instname')]);
    }

    /**
     * Returns the variable $varname of $_COOKIE and casts the value
     *
     * @param string $varname Variable name
     * @param string $vartype Variable type
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public static function cookie($varname, $vartype = '', $default = '')
    {
        return self::arrayKeyCast($_COOKIE, $varname, $vartype, $default);
    }

    /**
     * Returns the variable $varname of $_FILES and casts the value
     *
     * @param string $varname Variable name
     * @param string $vartype Variable type
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public static function files($varname, $vartype = '', $default = '')
    {
        return self::arrayKeyCast($_FILES, $varname, $vartype, $default);
    }

    /**
     * Returns the variable $varname of $_ENV and casts the value
     *
     * @param string $varname Variable name
     * @param string $vartype Variable type
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public static function env($varname, $vartype = '', $default = '')
    {
        return self::arrayKeyCast($_ENV, $varname, $vartype, $default);
    }

    /**
     * Searches the value $needle in array $haystack and returns the casted value
     *
     * @param array      $haystack Array
     * @param string|int $needle   Value to search
     * @param string     $vartype  Variable type
     * @param mixed      $default  Default value
     * @throws rex_exception
     * @return mixed
     */
    private static function arrayKeyCast(array $haystack, $needle, $vartype, $default = '')
    {
        if (!is_scalar($needle)) {
            throw new rex_exception('Scalar expected for $needle in arrayKeyCast()!');
        }

        if (array_key_exists($needle, $haystack)) {
            return rex_type::cast($haystack[$needle], $vartype);
        }

        if ($default === '') {
            return rex_type::cast($default, $vartype);
        }
        return $default;
    }

    /**
     * Returns the HTTP method of the current request
     *
     * @return String HTTP method in lowercase (head,get,post,put,delete)
     */
    public static function requestMethod()
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
    public static function isXmlHttpRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    /**
     * Returns true if the request is a PJAX-Request
     *
     * @see http://pjax.heroku.com/
     */
    public static function isPJAXRequest()
    {
        return isset($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] == 'true';
    }

    /**
     * Returns true when the current request is a PJAX-Request and the requested container matches the given $containerId
     *
     * @param string $containerId
     * @return bool
     */
    public static function isPJAXContainer($containerId)
    {
        if (!self::isPJAXRequest()) {
            return false;
        }

        return isset($_SERVER['HTTP_X_PJAX_CONTAINER']) && $_SERVER['HTTP_X_PJAX_CONTAINER'] == $containerId;
    }
}
