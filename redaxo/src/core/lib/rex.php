<?php

/**
 * REX base class for core properties etc.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex
{
    const CONFIG_NAMESPACE = 'core';

    /**
     * Array of properties.
     *
     * @var array
     */
    protected static $properties = [];

    /**
     * @see rex_config::set()
     */
    public static function setConfig($key, $value = null)
    {
        return rex_config::set(self::CONFIG_NAMESPACE, $key, $value);
    }

    /**
     * @see rex_config::get()
     */
    public static function getConfig($key = null, $default = null)
    {
        return rex_config::get(self::CONFIG_NAMESPACE, $key, $default);
    }

    /**
     * @see rex_config::has()
     */
    public static function hasConfig($key)
    {
        return rex_config::has(self::CONFIG_NAMESPACE, $key);
    }

    /**
     * @see rex_config::remove()
     */
    public static function removeConfig($key)
    {
        return rex_config::remove(self::CONFIG_NAMESPACE, $key);
    }

    /**
     * Sets a property.
     *
     * @param string $key   Key of the property
     * @param mixed  $value Value for the property
     *
     * @return bool TRUE when an existing value was overridden, otherwise FALSE
     *
     * @throws InvalidArgumentException on invalid parameters
     */
    public static function setProperty($key, $value)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        switch ($key) {
            case 'debug':
                // bc for boolean "debug" property
                if (!is_array($value)) {
                    $debug = self::getDebugFlags();
                    $debug['enabled'] = (bool) $value;
                    $value = $debug;
                }
                $value['enabled'] = isset($value['enabled']) && $value['enabled'];
                if (!isset($value['throw_always_exception']) || !$value['throw_always_exception']) {
                    $value['throw_always_exception'] = false;
                } elseif (is_array($value['throw_always_exception'])) {
                    $value['throw_always_exception'] = array_reduce($value['throw_always_exception'], function ($result, $item) {
                        if (is_string($item)) {
                            // $item is string, e.g. "E_WARNING"
                            $item = constant($item);
                        }

                        return $result | $item;
                    }, 0);
                }
                break;
            case 'server':
                if (!rex_validator::factory()->url($value)) {
                    throw new InvalidArgumentException('"' . $key . '" property: expecting $value to be a full URL!');
                }
                $value = rtrim($value, '/') . '/';
                break;
            case 'error_email':
                if (null !== $value && !rex_validator::factory()->email($value)) {
                    throw new InvalidArgumentException('"' . $key . '" property: expecting $value to be an email address!');
                }
                break;
            case 'console':
                if (null !== $value && !$value instanceof rex_console_application) {
                    throw new InvalidArgumentException(sprintf('"%s" property: expecting $value to be an instance of rex_console_application, "%s" found!', $key, is_object($value) ? get_class($value) : gettype($value)));
                }
        }
        $exists = isset(self::$properties[$key]);
        self::$properties[$key] = $value;
        return $exists;
    }

    /**
     * Returns a property.
     *
     * @param string $key     Key of the property
     * @param mixed  $default Default value, will be returned if the property isn't set
     *
     * @return mixed The value for $key or $default if $key cannot be found
     *
     * @throws InvalidArgumentException on invalid parameters
     */
    public static function getProperty($key, $default = null)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        if (isset(self::$properties[$key])) {
            return self::$properties[$key];
        }
        return $default;
    }

    /**
     * Returns if a property is set.
     *
     * @param string $key Key of the property
     *
     * @return bool TRUE if the key is set, otherwise FALSE
     */
    public static function hasProperty($key)
    {
        return is_string($key) && isset(self::$properties[$key]);
    }

    /**
     * Removes a property.
     *
     * @param string $key Key of the property
     *
     * @return bool TRUE if the value was found and removed, otherwise FALSE
     *
     * @throws InvalidArgumentException on invalid parameters
     */
    public static function removeProperty($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        $exists = isset(self::$properties[$key]);
        unset(self::$properties[$key]);
        return $exists;
    }

    /**
     * Returns if the setup is active.
     *
     * @return bool
     */
    public static function isSetup()
    {
        return (bool) self::getProperty('setup', false);
    }

    /**
     * Returns if the environment is the backend.
     *
     * @return bool
     */
    public static function isBackend()
    {
        return (bool) self::getProperty('redaxo', false);
    }

    /**
     * Returns the environment.
     *
     * @return string
     */
    public static function getEnvironment()
    {
        if (self::getConsole()) {
            return 'console';
        }

        return self::isBackend() ? 'backend' : 'frontend';
    }

    /**
     * Returns if the debug mode is active.
     *
     * @return bool
     */
    public static function isDebugMode()
    {
        $debug = self::getDebugFlags();

        return isset($debug['enabled']) && $debug['enabled'];
    }

    /**
     * Returns the debug flags.
     *
     * @return array
     */
    public static function getDebugFlags()
    {
        return self::getProperty('debug');
    }

    /**
     * Returns if the safe mode is active.
     *
     * @return bool
     */
    public static function isSafeMode()
    {
        return self::isBackend() && PHP_SESSION_ACTIVE == session_status() && rex_session('safemode', 'boolean', false);
    }

    /**
     * Returns the table prefix.
     *
     * @return string
     */
    public static function getTablePrefix()
    {
        return self::getProperty('table_prefix');
    }

    /**
     * Adds the table prefix to the table name.
     *
     * @param string $table Table name
     *
     * @return string
     */
    public static function getTable($table)
    {
        return self::getTablePrefix() . $table;
    }

    /**
     * Returns the temp prefix.
     *
     * @return string
     */
    public static function getTempPrefix()
    {
        return self::getProperty('temp_prefix');
    }

    /**
     * Returns the current user.
     *
     * @return null|rex_user
     */
    public static function getUser()
    {
        return self::getProperty('user');
    }

    /**
     * Returns the current impersonator user.
     *
     * @return null|rex_user
     */
    public static function getImpersonator()
    {
        $login = self::getProperty('login');

        return $login ? $login->getImpersonator() : null;
    }

    /**
     * Returns the console application.
     *
     * @return null|rex_console_application
     */
    public static function getConsole()
    {
        return self::getProperty('console', null);
    }

    /**
     * Returns the server URL.
     *
     * @param null|string $protocol
     *
     * @return string
     */
    public static function getServer($protocol = null)
    {
        if (null === $protocol) {
            return self::getProperty('server');
        }
        list(, $server) = explode('://', self::getProperty('server'), 2);
        return $protocol ? $protocol . '://' . $server : $server;
    }

    /**
     * Returns the server name.
     *
     * @return string
     */
    public static function getServerName()
    {
        return self::getProperty('servername');
    }

    /**
     * Returns the error email.
     *
     * @return string
     */
    public static function getErrorEmail()
    {
        return self::getProperty('error_email');
    }

    /**
     * Returns the redaxo version.
     *
     * @param string $format See {@link rex_formatter::version()}
     *
     * @return string
     */
    public static function getVersion($format = null)
    {
        $version = self::getProperty('version');

        if ($format) {
            return rex_formatter::version($version, $format);
        }
        return $version;
    }

    /**
     * Returns the current git version hash for the given path.
     *
     * @param string $path A local filesystem path
     *
     * @return false|string
     */
    public static function getVersionHash($path)
    {
        static $gitHash = [];

        if (!isset($gitHash[$path])) {
            $gitHash[$path] = false; // exec only once
            $output = '';
            $exitCode = null;

            $command = 'which git 2>&1 1>/dev/null && cd '. escapeshellarg($path) .' && git show --oneline -s';
            @exec($command, $output, $exitCode);
            if ($exitCode === 0) {
                $output = implode('', $output);
                if (preg_match('{^[0-9a-f]+}', $output, $matches)) {
                    $gitHash[$path] = $matches[0];
                }
            }
        }

        return $gitHash[$path];
    }

    /**
     * Returns the title tag and if the property "use_accesskeys" is true, the accesskey tag.
     *
     * @param string $title Title
     * @param string $key   Key for the accesskey
     *
     * @return string
     */
    public static function getAccesskey($title, $key)
    {
        if (self::getProperty('use_accesskeys')) {
            $accesskeys = (array) self::getProperty('accesskeys', []);
            if (isset($accesskeys[$key])) {
                return ' accesskey="' . $accesskeys[$key] . '" title="' . $title . ' [' . $accesskeys[$key] . ']"';
            }
        }

        return ' title="' . $title . '"';
    }

    /**
     * Returns the file perm.
     *
     * @return int
     */
    public static function getFilePerm()
    {
        return (int) self::getProperty('fileperm', 0664);
    }

    /**
     * Returns the dir perm.
     *
     * @return int
     */
    public static function getDirPerm()
    {
        return (int) self::getProperty('dirperm', 0775);
    }
}
