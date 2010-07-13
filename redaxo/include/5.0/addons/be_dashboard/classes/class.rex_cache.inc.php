<?php

define('REX_CACHE_SEPARATOR', ':');
define('REX_CACHE_CLEAN_OLD', 1);
define('REX_CACHE_CLEAN_ALL', 2);

/**
 * rex_cache is an abstract class for all cache classes.
 * inspired by the symfony caching framework.
 *
 * @author fabien[dot]potencier[at]symfony-project[dot]com Fabien Potencier
 * @author <a href="http://www.symfony-project.org/">www.symfony-project.org</a>
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

/*abstract*/ class rex_cache
{
  var $options;

  /**
   * Available options:
   *
   * * cleanMode: The automatic cleaning process destroy too old (for the given life time) (default value: 1000)
   *   cache files when a new cache file is written.
   *     0               => no automatic cache cleaning
   *     1               => systematic cache cleaning
   *     x (integer) > 1 => automatic cleaning randomly 1 times on x cache write
   *
   * * lifetime (optional): The default life time (default value: 86400)
   */
  /*public*/function rex_cache($options = array())
  {
    $this->options = array_merge(array(
      'automatic_cleaning_factor' => 1000,
      'lifetime'                  => 86400,
      'prefix'                    => md5(dirname(__FILE__)),
    ), $options);

    $this->options['prefix'] .= REX_CACHE_SEPARATOR;
  }

  /**
   * Gets the cache content for a given key.
   *
   * @param string $key     The cache key
   * @param mixed  $default The default value is the key does not exist or not valid anymore
   *
   * @return mixed The data of the cache
   */
  /*abstract public*/ function get($key, $default = null){}

  /**
   * Returns true if there is a cache for the given key.
   *
   * @param string $key The cache key
   *
   * @return Boolean true if the cache exists, false otherwise
   */
  /*abstract public*/ function has($key){}

  /**
   * Saves some data in the cache.
   *
   * @param string $key      The cache key
   * @param mixed  $data     The data to put in cache
   * @param int    $lifetime The lifetime
   *
   * @return Boolean true if no problem
   */
  /*abstract public*/ function set($key, $data, $lifetime = null){}

  /**
   * Removes a content from the cache.
   *
   * @param string $key The cache key
   *
   * @return Boolean true if no problem
   */
  /*abstract public*/ function remove($key){}

  /**
   * Removes content from the cache that matches the given pattern.
   *
   * @param string $pattern The cache key pattern
   *
   * @return Boolean true if no problem
   *
   * @see patternToRegexp
   */
  /*abstract public*/ function removePattern($pattern){}

  /**
   * Cleans the cache.
   *
   * @param string $mode The clean mode
   *                     sfCache::ALL: remove all keys (default)
   *                     sfCache::OLD: remove all expired keys
   *
   * @return Boolean true if no problem
   */
  /*abstract public*/ function clean($mode = REX_CACHE_SEPARATOR_ALL){}

  /**
   * Returns the timeout for the given key.
   *
   * @param string $key The cache key
   *
   * @return int The timeout time
   */
  /*abstract public*/ function getTimeout($key){}

  /**
   * Returns the last modification date of the given key.
   *
   * @param string $key The cache key
   *
   * @return int The last modified time
   */
  /*abstract public*/ function getLastModified($key){}

  /**
   * Computes lifetime.
   *
   * @param integer $lifetime Lifetime in seconds
   *
   * @return integer Lifetime in seconds
   */
  /*public*/ function getLifetime($lifetime)
  {
    return null === $lifetime ? $this->getOption('lifetime') : $lifetime;
  }

  /**
   * Gets many keys at once.
   *
   * @param array $keys An array of keys
   *
   * @return array An associative array of data from cache
   */
  /*public*/ function getMany($keys)
  {
    $data = array();
    foreach ($keys as $key)
    {
      $data[$key] = $this->get($key);
    }

    return $data;
  }

  /**
   * Converts a pattern to a regular expression.
   *
   * A pattern can use some special characters:
   *
   *  - * Matches a namespace (foo:*:bar)
   *  - ** Matches one or more namespaces (foo:**:bar)
   *
   * @param string $pattern A pattern
   *
   * @return string A regular expression
   */
  /*protected*/ function patternToRegexp($pattern)
  {
    $regexp = str_replace(
      array('\\*\\*', '\\*'),
      array('.+?',    '[^'.preg_quote(REX_CACHE_SEPARATOR, '#').']+'),
      preg_quote($pattern, '#')
    );

    return '#^'.$regexp.'$#';
  }

  /**
   * Gets an option value.
   *
   * @param string $name    The option name
   * @param mixed  $default The default value
   *
   * @return mixed The option value
   */
  /*public*/ function getOption($name, $default = null)
  {
    return isset($this->options[$name]) ? $this->options[$name] : $default;
  }

  /**
   * Sets an option value.
   *
   * @param string $name  The option name
   * @param mixed  $value The option value
   */
  /*public*/ function setOption($name, $value)
  {
    return $this->options[$name] = $value;
  }
}