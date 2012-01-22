<?php

/**
 * REDAXO Autoloader.
 *
 * This class was originally copied from the Symfony Framework:
 * Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * Adjusted in very many places
 *
 * @package redaxo5
 * @version svn:$Id$
 */
class rex_autoload
{
  static protected
    $registered = false,
    $cacheFile    = null,
    $cacheChanged = false,
    $reloaded     = false,
    $dirs         = array(),
    $addedDirs    = array(),
    $classes      = array();

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
      throw new Exception(sprintf('Unable to register %s::autoload as an autoloading method.', __CLASS__));
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
    if(class_exists($class, false) || interface_exists($class, false)
       || function_exists('trait_exists') && trait_exists($class, false))
    {
      return true;
    }

    // we have a class path for the class, let's include it
    if(isset(self::$classes[$class]) && is_readable(self::$classes[$class]))
    {
      require self::$classes[$class];
    }

    if(class_exists($class, false) || interface_exists($class, false)
       || function_exists('trait_exists') && trait_exists($class, false))
    {
      return true;
    }
    elseif(!self::$reloaded)
    {
      self::reload();
      return self::autoload($class);
    }

    return false;
  }

  /**
   * Loads the cache.
   */
  static private function loadCache()
  {
    if (!self::$cacheFile || !is_readable(self::$cacheFile))
    {
      return;
    }

    list(self::$classes, self::$dirs) = json_decode(file_get_contents(self::$cacheFile), true);
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
        file_put_contents(self::$cacheFile, json_encode(array(self::$classes, self::$dirs)));
        self::$cacheChanged = false;
      }
      else
      {
        throw new Exception("Unable to write autoload cachefile '". self::$cacheFile ."'!");
      }
    }
  }

  /**
   * Reloads cache.
   */
  static public function reload()
  {
    self::$classes = array();
    self::$dirs = array();

    foreach (self::$addedDirs as $dir)
    {
      self::_addDirectory($dir);
    }

    self::$cacheChanged = true;
    self::$reloaded = true;
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
   */
  static public function addDirectory($dir)
  {
    $dir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
    self::$addedDirs[] = $dir;
    if(!in_array($dir, self::$dirs))
    {
      self::_addDirectory($dir);
      self::$dirs[] = $dir;
      self::$cacheChanged = true;
    }
  }

  static private function _addDirectory($dir)
  {
    if($files = glob($dir .'*.php', GLOB_NOSORT))
    {
      foreach($files as $file)
      {
        self::addFile($file);
      }
    }

    if($subdirs = glob($dir .'*', GLOB_ONLYDIR | GLOB_NOSORT | GLOB_MARK))
    {
      // recursive over subdirectories
      foreach($subdirs as $subdir)
      {
        self::_addDirectory($subdir);
      }
    }
  }

  static private function addFile($file)
  {
    if(!is_file($file))
    {
      return;
    }

    $namespace = '';
    $tokens = token_get_all(file_get_contents($file));
    $count = count($tokens);
    $classTokens = array(T_CLASS, T_INTERFACE);
    if(defined('T_TRAIT'))
      $classTokens[] = T_TRAIT;
    for($i = 0; $i < $count; ++$i)
    {
      if(is_array($tokens[$i]))
      {
        if($tokens[$i][0] == T_NAMESPACE)
        {
          $namespace = '';
          for(++$i; isset($tokens[$i][0]) && in_array($tokens[$i][0], array(T_WHITESPACE, T_NS_SEPARATOR, T_STRING)); ++$i)
            $namespace .= $tokens[$i][0] == T_WHITESPACE ? '' : $tokens[$i][1];
          $namespace .= empty($namespace) ? '' : '\\';
        }
        if(in_array($tokens[$i][0], $classTokens))
        {
          $i += 2;
          if(isset($tokens[$i][0]) && $tokens[$i][0] == T_STRING)
          {
            $class = strtolower($namespace . $tokens[$i][1]);
            self::$classes[$class] = $file;
          }
        }
      }
    }
  }
}