<?php

/**
 * REDAXO Autoloader.
 * 
 * This class was mainly copied from the Symfony Framework:
 * Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * @package redaxo4
 * @version svn:$Id$
 */
class rex_autoload
{
  static protected
    $registered = false,
    $instance   = null;

  protected
    $cacheFile    = null,
    $cacheLoaded  = false,
    $cacheChanged = false,
    $dirs         = array(),
    $files        = array(),
    $classes      = array(),
    $overriden    = array();

  protected function __construct($cacheFile = null)
  {
    if (null !== $cacheFile)
    {
      $this->cacheFile = $cacheFile;
    }

    $this->loadCache();
  }

  /**
   * Retrieves the singleton instance of this class.
   *
   * @param  string $cacheFile  The file path to save the cache
   *
   * @return rex_autoload   A rex_autoload implementation instance.
   */
  static public function getInstance($cacheFile = null)
  {
    if (!isset(self::$instance))
    {
      self::$instance = new rex_autoload($cacheFile);
    }

    return self::$instance;
  }

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
    if (false === spl_autoload_register(array(self::getInstance(), 'autoload')))
    {
      throw new sfException(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::getInstance())));
    }

    if (self::getInstance()->cacheFile)
    {
      register_shutdown_function(array(self::getInstance(), 'saveCache'));
    }

    self::$registered = true;
  }

  /**
   * Unregister rex_autoload from spl autoloader.
   *
   * @return void
   */
  static public function unregister()
  {
    spl_autoload_unregister(array(self::getInstance(), 'autoload'));
    self::$registered = false;
  }

  /**
   * Handles autoloading of classes.
   *
   * @param  string $class A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    $class = strtolower($class);

    // class already exists
    if (class_exists($class, false) || interface_exists($class, false))
    {
      return true;
    }

    // we have a class path for the class, let's include it
    if (isset($this->classes[$class]))
    {
      require $this->classes[$class];

      return true;
    }

    return false;
  }

  /**
   * Loads the cache.
   */
  public function loadCache()
  {
    if (!$this->cacheFile || !is_readable($this->cacheFile))
    {
      return;
    }

    list($this->classes, $this->dirs, $this->files) = unserialize(file_get_contents($this->cacheFile));

    $this->cacheLoaded = true;
    $this->cacheChanged = false;
  }

  /**
   * Saves the cache.
   */
  public function saveCache()
  {
    if ($this->cacheChanged)
    {
      if (is_writable(dirname($this->cacheFile)))
      {
        file_put_contents($this->cacheFile, serialize(array($this->classes, $this->dirs, $this->files)));
        $this->cacheChanged = false;
      }
      else
      {
        throw new rexException("Unable to write autoload cachefile '"+ $this->cacheFile +"'!");
      }
    }
  }

  /**
   * Reloads cache.
   */
  public function reload()
  {
    $this->classes = array();
    $this->cacheLoaded = false;

    foreach ($this->dirs as $dir)
    {
      $this->addDirectory($dir);
    }

    foreach ($this->files as $file)
    {
      $this->addFile($file);
    }

    foreach ($this->overriden as $class => $path)
    {
      $this->classes[$class] = $path;
    }

    $this->cacheLoaded = true;
    $this->cacheChanged = true;
  }

  /**
   * Removes the cache.
   */
  public function removeCache()
  {
    @unlink($this->cacheFile);
  }

  /**
   * Adds a directory to the autoloading system if not yet present and give it the highest possible precedence.
   *
   * @param string $dir The directory to look for classes
   * @param string $ext The extension to look for
   */
  public function addDirectory($classdir, $ext = '.php')
  {
    if ($dirs = glob($classdir .'*'. $ext))
    {
      foreach ($dirs as $dir)
      {
        if (false !== $key = array_search($dir, $this->dirs))
        {
          unset($this->dirs[$key]);
          $this->dirs[] = $dir;

          if ($this->cacheLoaded)
          {
            continue;
          }
        }
        else
        {
          $this->dirs[] = $dir;
        }

        $this->cacheChanged = true;
        $this->addFile($dir, false);
      }
    }
    
    if($subdirs = glob($classdir .'*', GLOB_ONLYDIR))
    {
      // recursive over subdirectories
      foreach($subdirs as $subdir) {
        $this->addDirectory($subdir .'/');
      }
    }
  }
  
  /**
   * Adds files to the autoloading system.
   *
   * @param array   $files    An array of files
   * @param Boolean $register Whether to register those files as single entities (used when reloading)
   */
  public function addFiles(array $files, $register = true)
  {
    foreach ($files as $file)
    {
      $this->addFile($file, $register);
    }
  }

  /**
   * Adds a file to the autoloading system.
   *
   * @param string  $file     A file path
   * @param Boolean $register Whether to register those files as single entities (used when reloading)
   */
  public function addFile($file, $register = true)
  {
    if (!is_file($file))
    {
      return;
    }

    if (in_array($file, $this->files))
    {
      if ($this->cacheLoaded)
      {
        return;
      }
    }
    else
    {
      if ($register)
      {
        $this->files[] = $file;
      }
    }

    if ($register)
    {
      $this->cacheChanged = true;
    }

    preg_match_all('~^\s*(?:abstract\s+|final\s+)?(?:class|interface)\s+(\w+)~mi', file_get_contents($file), $classes);
    foreach ($classes[1] as $class)
    {
      $this->classes[strtolower($class)] = $file;
    }
  }

  /**
   * Sets the path for a particular class.
   *
   * @param string $class A PHP class name
   * @param string $path  An absolute path
   */
  public function setClassPath($class, $path)
  {
    $class = strtolower($class);

    $this->overriden[$class] = $path;

    $this->classes[$class] = $path;
  }

  /**
   * Returns the path where a particular class can be found.
   *
   * @param string $class A PHP class name
   *
   * @return string|null An absolute path
   */
  public function getClassPath($class)
  {
    $class = strtolower($class);

    return isset($this->classes[$class]) ? $this->classes[$class] : null;
  }
}