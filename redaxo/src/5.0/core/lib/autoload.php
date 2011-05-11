<?php

/**
 * REDAXO Autoloader.
 *
 * This class was mainly copied from the Symfony Framework:
 * Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * Adjusted in the following places
 * - file-cache uses json instead of a serialized php array to boost performance
 *
 * @package redaxo5
 * @version svn:$Id$
 */
class rex_autoload
{
  static protected
    $registered = false,
    $cacheFile    = null,
    $cacheLoaded  = false,
    $cacheChanged = false,
    $dirs         = array(),
    $files        = array(),
    $classes      = array(),
    $overriden    = array();

  /**
   * Register rex_autoload in spl autoloader.
   *
   * @return void
   */
  static public function register()
  {
    if (self::$registered)
    {
      return;
    }

    ini_set('unserialize_callback_func', 'spl_autoload_call');
    if (false === spl_autoload_register(array(__CLASS__, 'autoload')))
    {
      throw new sfException(sprintf('Unable to register %s::autoload as an autoloading method.', __CLASS__));
    }

    self::$cacheFile = rex_path::cache('autoload.cache');
    self::loadCache();
    register_shutdown_function(array(__CLASS__, 'saveCache'));

    self::$registered = true;
  }

  /**
   * Unregister rex_autoload from spl autoloader.
   *
   * @return void
   */
  static public function unregister()
  {
    spl_autoload_unregister(array(__CLASS__, 'autoload'));
    self::$registered = false;
  }

  /**
   * Handles autoloading of classes.
   *
   * @param  string $class A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  static public function autoload($class)
  {
    $class = strtolower($class);

    // class already exists
    if (class_exists($class, false) || interface_exists($class, false))
    {
      return true;
    }

    // we have a class path for the class, let's include it
    if (isset(self::$classes[$class]))
    {
      require self::$classes[$class];

      return true;
    }

    return false;
  }

  /**
   * Loads the cache.
   */
  static public function loadCache()
  {
    if (!self::$cacheFile || !is_readable(self::$cacheFile))
    {
      return;
    }

    list(self::$classes, self::$dirs, self::$files) = json_decode(file_get_contents(self::$cacheFile), true);

    self::$cacheLoaded = true;
    self::$cacheChanged = false;
  }

  /**
   * Saves the cache.
   */
  static public function saveCache()
  {
    if (self::$cacheChanged)
    {
      if (is_writable(dirname(self::$cacheFile)))
      {
        file_put_contents(self::$cacheFile, json_encode(array(self::$classes, self::$dirs, self::$files)));
        self::$cacheChanged = false;
      }
      else
      {
        throw new rexException("Unable to write autoload cachefile '"+ self::$cacheFile +"'!");
      }
    }
  }

  /**
   * Reloads cache.
   */
  static public function reload()
  {
    self::$classes = array();
    self::$cacheLoaded = false;

    foreach (self::$dirs as $dir)
    {
      self::addDirectory($dir);
    }

    foreach (self::$files as $file)
    {
      self::addFile($file);
    }

    foreach (self::$overriden as $class => $path)
    {
      self::$classes[$class] = $path;
    }

    self::$cacheLoaded = true;
    self::$cacheChanged = true;
  }

  /**
   * Removes the cache.
   */
  static public function removeCache()
  {
    rex_file::delete(self::$cacheFile);
  }

  /**
   * Adds a directory to the autoloading system if not yet present and give it the highest possible precedence.
   *
   * @param string $dir The directory to look for classes
   * @param string $ext The extension to look for
   */
  static public function addDirectory($classdir, $ext = '.php')
  {
    if ($dirs = glob($classdir .'*'. $ext))
    {
      foreach ($dirs as $dir)
      {
        if (false !== $key = array_search($dir, self::$dirs))
        {
          unset(self::$dirs[$key]);
          self::$dirs[] = $dir;

          if (self::$cacheLoaded)
          {
            continue;
          }
        }
        else
        {
          self::$dirs[] = $dir;
        }

        self::$cacheChanged = true;
        self::addFile($dir, false);
      }
    }

    if($subdirs = glob($classdir .'*', GLOB_ONLYDIR))
    {
      // recursive over subdirectories
      foreach($subdirs as $subdir) {
        self::addDirectory($subdir .DIRECTORY_SEPARATOR);
      }
    }
  }

  /**
   * Adds files to the autoloading system.
   *
   * @param array   $files    An array of files
   * @param Boolean $register Whether to register those files as single entities (used when reloading)
   */
  static public function addFiles(array $files, $register = true)
  {
    foreach ($files as $file)
    {
      self::addFile($file, $register);
    }
  }

  /**
   * Adds a file to the autoloading system.
   *
   * @param string  $file     A file path
   * @param Boolean $register Whether to register those files as single entities (used when reloading)
   */
  static public function addFile($file, $register = true)
  {
    if (!is_file($file))
    {
      return;
    }

    if (in_array($file, self::$files))
    {
      if (self::$cacheLoaded)
      {
        return;
      }
    }
    else
    {
      if ($register)
      {
        self::$files[] = $file;
      }
    }

    if ($register)
    {
      self::$cacheChanged = true;
    }

    preg_match_all('~^\s*(?:abstract\s+|final\s+)?(?:class|interface)\s+(\w+)~mi', file_get_contents($file), $classes);
    foreach ($classes[1] as $class)
    {
      self::$classes[strtolower($class)] = $file;
    }
  }

  /**
   * Sets the path for a particular class.
   *
   * @param string $class A PHP class name
   * @param string $path  An absolute path
   */
  static public function setClassPath($class, $path)
  {
    $class = strtolower($class);

    self::$overriden[$class] = $path;

    self::$classes[$class] = $path;
  }

  /**
   * Returns the path where a particular class can be found.
   *
   * @param string $class A PHP class name
   *
   * @return string|null An absolute path
   */
  static public function getClassPath($class)
  {
    $class = strtolower($class);

    return isset(self::$classes[$class]) ? self::$classes[$class] : null;
  }
}