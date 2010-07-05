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

define('REX_CACHE_FILE_READ_DATA', 1);
define('REX_CACHE_FILE_READ_TIMEOUT', 2);
define('REX_CACHE_FILE_READ_LAST_MODIFIED', 4);
define('REX_CACHE_FILE_EXTENSION', '.cache');

class rex_file_cache extends rex_cache
{
  /**
   * Initializes this rex_cache instance.
   *
   * Available options:
   *
   * * cache_dir: The directory where to put cache files
   *
   * * see rex_cache for options available for all drivers
   *
   * @see rex_cache
   */
  /*public*/ function rex_file_cache($options = array())
  {
    global $REX;

    parent::rex_cache($options);

    if (!$this->getOption('cache_dir'))
    {
      $this->setOption('cache_dir', $REX['INCLUDE_PATH'] .'/generated/cache');
    }

    $this->setcache_dir($this->getOption('cache_dir'));
  }

  /**
   * @see rex_cache
   */
  /*public*/ function get($key, $default = null)
  {
    $file_path = $this->getFilePath($key);
    if (!file_exists($file_path))
    {
      return $default;
    }

    $data = $this->read($file_path, REX_CACHE_FILE_READ_DATA);

    if ($data[REX_CACHE_FILE_READ_DATA] === null)
    {
      return $default;
    }

    return $data[REX_CACHE_FILE_READ_DATA];
  }

  /**
   * @see rex_cache
   */
  /*public*/ function has($key)
  {
    $path = $this->getFilePath($key);
    return file_exists($path) && $this->isValid($path);
  }

  /**
   * @see rex_cache
   */
  /*public*/ function set($key, $data, $lifetime = null)
  {
    if ($this->getOption('automatic_cleaning_factor') > 0 && rand(1, $this->getOption('automatic_cleaning_factor')) == 1)
    {
      $this->clean(REX_CACHE_CLEAN_OLD);
    }

    return $this->write($this->getFilePath($key), $data, time() + $this->getLifetime($lifetime));
  }

  /**
   * @see rex_cache
   */
  /*public*/ function remove($key)
  {
    return @unlink($this->getFilePath($key));
  }

  /**
   * @see rex_cache
   */
  /*public*/ function removePattern($pattern)
  {
    if (false !== strpos($pattern, '**'))
    {
      $pattern = str_replace(REX_CACHE_SEPARATOR, DIRECTORY_SEPARATOR, $pattern).REX_CACHE_FILE_EXTENSION;

      $regexp = self::patternToRegexp($pattern);
      $paths = array();
      foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getOption('cache_dir'))) as $path)
      {
        if (preg_match($regexp, str_replace($this->getOption('cache_dir').DIRECTORY_SEPARATOR, '', $path)))
        {
          $paths[] = $path;
        }
      }
    }
    else
    {
      $paths = glob($this->getOption('cache_dir').DIRECTORY_SEPARATOR.str_replace(REX_CACHE_SEPARATOR, DIRECTORY_SEPARATOR, $pattern).REX_CACHE_FILE_EXTENSION);
    }

    foreach ($paths as $path)
    {
      if (is_dir($path))
      {
        sfToolkit::clearDirectory($path);
      }
      else
      {
        @unlink($path);
      }
    }
  }

  /**
   * @see rex_cache
   */
  /*public*/ function clean($mode = REX_CACHE_CLEAN_ALL)
  {
    if (!is_dir($this->getOption('cache_dir')))
    {
      return true;
    }

    $result = true;
    // TODO PHP4 Compat!
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getOption('cache_dir'))) as $file)
    {
      if (REX_CACHE_CLEAN_ALL == $mode || !$this->isValid($file))
      {
        $result = @unlink($file) && $result;
      }
    }

    return $result;
  }

  /**
   * @see rex_cache
   */
  /*public*/ function getTimeout($key)
  {
    $path = $this->getFilePath($key);

    if (!file_exists($path))
    {
      return 0;
    }

    $data = $this->read($path, REX_CACHE_FILE_READ_TIMEOUT);

    return $data[REX_CACHE_FILE_READ_TIMEOUT] < time() ? 0 : $data[REX_CACHE_FILE_READ_TIMEOUT];
  }

  /**
   * @see rex_cache
   */
  /*public*/ function getLastModified($key)
  {
    $path = $this->getFilePath($key);

    if (!file_exists($path))
    {
      return 0;
    }

    $data = $this->read($path, REX_CACHE_FILE_READ_TIMEOUT | REX_CACHE_FILE_READ_LAST_MODIFIED);

    if ($data[REX_CACHE_FILE_READ_TIMEOUT] < time())
    {
      return 0;
    }
    return $data[REX_CACHE_FILE_READ_LAST_MODIFIED];
  }

  /*protected*/ function isValid($path)
  {
    $data = $this->read($path, REX_CACHE_FILE_READ_TIMEOUT);
    return time() < $data[REX_CACHE_FILE_READ_TIMEOUT];
  }

  /**
   * Converts a cache key to a full path.
   *
   * @param string $key The cache key
   *
   * @return string The full path to the cache file
   */
  /*protected*/ function getFilePath($key)
  {
    return $this->getOption('cache_dir').DIRECTORY_SEPARATOR.str_replace(REX_CACHE_SEPARATOR, DIRECTORY_SEPARATOR, $key).REX_CACHE_FILE_EXTENSION;
  }

  /**
   * Reads the cache file and returns the content.
   *
   * @param string $path The file path
   * @param mixed  $type The type of data you want to be returned
   *                     REX_CACHE_FILE_READ_DATA: The cache content
   *                     REX_CACHE_FILE_READ_TIMEOUT: The timeout
   *                     REX_CACHE_FILE_READ_LAST_MODIFIED: The last modification timestamp
   *
   * @return array the (meta)data of the cache file. E.g. $data[sfFileCache::READ_DATA]
   */
  /*protected*/ function read($path, $type = REX_CACHE_FILE_READ_DATA)
  {
    if (!$fp = @fopen($path, 'rb'))
    {
      trigger_error(sprintf('Unable to read cache file "%s".', $path), E_USER_ERROR);
    }

    @flock($fp, LOCK_SH);
    $data[REX_CACHE_FILE_READ_TIMEOUT] = intval(@fread($fp, 12));
//    $data[REX_CACHE_FILE_READ_TIMEOUT] = intval(@stream_get_contents($fp, 12, 0));
    if ($type != REX_CACHE_FILE_READ_TIMEOUT && time() < $data[REX_CACHE_FILE_READ_TIMEOUT])
    {
      if ($type & REX_CACHE_FILE_READ_LAST_MODIFIED)
      {
        $data[REX_CACHE_FILE_READ_LAST_MODIFIED] = intval(@fread($fp, 12, 12));
//        $data[REX_CACHE_FILE_READ_LAST_MODIFIED] = intval(@stream_get_contents($fp, 12, 12));
      }
      if ($type & REX_CACHE_FILE_READ_DATA)
      {
        fseek($fp, 0, SEEK_END);
        $length = ftell($fp) - 24;
        fseek($fp, 24);
        $data[REX_CACHE_FILE_READ_DATA] = @fread($fp, $length);
      }
    }
    else
    {
      $data[REX_CACHE_FILE_READ_LAST_MODIFIED] = null;
      $data[REX_CACHE_FILE_READ_DATA] = null;
    }
    @flock($fp, LOCK_UN);
    @fclose($fp);

    return $data;
  }

  /**
   * Writes the given data in the cache file.
   *
   * @param string  $path    The file path
   * @param string  $data    The data to put in cache
   * @param integer $timeout The timeout timestamp
   *
   * @return boolean true if ok, otherwise false
   */
  /*protected*/ function write($path, $data, $timeout)
  {
    $current_umask = umask();
    umask(0000);

    if (!is_dir(dirname($path)))
    {
      // STM: Keep PHP4 compat
      // mkdir(dirname($path), 0777, true);
      // create directory structure if needed
      mkdir(dirname($path), 0777);
    }

    $tmpFile = tempnam(dirname($path), basename($path));

    if (!$fp = @fopen($tmpFile, 'wb'))
    {
      trigger_error(sprintf('Unable to write cache file "%s".', $tmpFile), E_USER_ERROR);
    }

    @fwrite($fp, str_pad($timeout, 12, 0, STR_PAD_LEFT));
    @fwrite($fp, str_pad(time(), 12, 0, STR_PAD_LEFT));
    @fwrite($fp, $data);
    @fclose($fp);

    // Hack from Agavi (http://trac.agavi.org/changeset/3979)
    // With php < 5.2.6 on win32, renaming to an already existing file doesn't work, but copy does,
    // so we simply assume that when rename() fails that we are on win32 and try to use copy()
    if (!@rename($tmpFile, $path))
    {
      if (copy($tmpFile, $path))
      {
        unlink($tmpFile);
      }
    }

    chmod($path, 0666);
    umask($current_umask);

    return true;
  }

  /**
   * Sets the cache root directory.
   *
   * @param string $cache_dir The directory where to put the cache files
   */
  /*protected*/ function setcache_dir($cache_dir)
  {
    // remove last DIRECTORY_SEPARATOR
    if (DIRECTORY_SEPARATOR == substr($cache_dir, -1))
    {
      $cache_dir = substr($cache_dir, 0, -1);
    }

    // create cache dir if needed
    if (!is_dir($cache_dir))
    {
      $current_umask = umask(0000);
      @mkdir($cache_dir, 0777, true);
      umask($current_umask);
    }
  }
}
