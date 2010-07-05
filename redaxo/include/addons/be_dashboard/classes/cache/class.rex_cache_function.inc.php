<?php

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
class rex_function_cache
{
  var $cache = null;

  /**
   * Constructor.
   *
   * @param sfCache $cache An sfCache object instance
   */
  /*public*/ function rex_function_cache(/*rex_cache_*/ $cache)
  {
    $this->cache = $cache;
  }

  /**
   * Calls a cacheable function or method (or not if there is already a cache for it).
   *
   * Arguments of this method are read with func_get_args. So it doesn't appear in the function definition.
   *
   * The first argument can be any PHP callable:
   *
   * $cache->call('functionName', array($arg1, $arg2));
   * $cache->call(array($object, 'methodName'), array($arg1, $arg2));
   *
   * @param mixed $callable  A PHP callable
   * @param array $arguments An array of arguments to pass to the callable
   *
   * @return mixed The result of the function/method
   */
  /*public*/ function call($callable, $arguments = array())
  {
    // Generate a cache id
    $key = $this->computeCacheKey($callable, $arguments);

    $serialized = $this->cache->get($key);
    if ($serialized !== null)
    {
      $data = unserialize($serialized);
    }
    else
    {
      $data = array();

      if (!is_callable($callable))
      {
        trigger_error('The first argument to call() must be a valid callable.', E_USER_ERROR);
      }

      ob_start();
      ob_implicit_flush(false);

      $data['result'] = call_user_func_array($callable, $arguments);
      $data['output'] = ob_get_clean();

      $this->cache->set($key, serialize($data));
    }

    echo $data['output'];

    return $data['result'];
  }

  /**
   * Returns the cache instance.
   *
   * @return sfCache The sfCache instance
   */
  /*public*/ function getCache()
  {
    return $this->cache;
  }

  /**
   * Computes the cache key for a given callable and the arguments.
   *
   * @param mixed $callable  A PHP callable
   * @param array $arguments An array of arguments to pass to the callable
   *
   * @return string The associated cache key
   */
  /*public*/ function computeCacheKey($callable, $arguments = array())
  {
    return md5(serialize($callable).serialize($arguments));
  }
}
