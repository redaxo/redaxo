<?php

/**
 * Directory iterator
 *
 * @author gharlan
 */
class rex_dir_iterator extends RecursiveFilterIterator
{
  const ALL = 'IGNORE_ALL';

  private
    $ignoreDirs = array(),
    $ignoreDirsRecursive = true,
    $ignoreFiles = array(),
    $ignoreFilesRecursive = true,
    $ignorePrefixes = array(),
    $ignorePrefixesRecursive = true,
    $ignoreSuffixes = array(),
    $ignoreSuffixesRecursive = true,
    $ignoreSystemStuff = false;

  static private
    $systemStuff = array('.DS_Store', 'Thumbs.db', 'desktop.ini', '.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg');

  /**
   * Constructor
   *
   * @param RecursiveDirectoryIterator $iterator Inner iterator
   */
  public function __construct(RecursiveDirectoryIterator $iterator)
  {
    parent::__construct($iterator);
  }

  /**
   * Ignores directories
   *
   * @param string|array $dirnames Directory name or an array of directory names
   * @param boolean $recursive When FALSE the dirnames won't be checked in child directories
   * @return rex_dir_iterator The current iterator
   */
  public function ignoreDirs($dirnames = self::ALL, $recursive = true)
  {
    $this->ignoreDirs = $dirnames == self::ALL ? self::ALL : (array) $dirnames;
    $this->ignoreDirsRecursive = $recursive;

    return $this;
  }

  /**
   * Ignores files
   *
   * @param string|array $filenames Filename or an array of filenames
   * @param boolean $recursive When FALSE the filenames won't be checked in child directories
   * @return rex_dir_iterator The current iterator
   */
  public function ignoreFiles($filenames = self::ALL, $recursive = true)
  {
    $this->ignoreFiles = $filenames == self::ALL ? self::ALL : (array) $filenames;
    $this->ignoreFilesRecursive = $recursive;

    return $this;
  }

  /**
   * Ignores directories and files by prefixes
   *
   * @param string|array $prefixes A prefix or an array of prefixes
   * @param boolean $recursive When FALSE the prefixes won't be checked in child directories
   * @return rex_dir_iterator The current iterator
   */
  public function ignorePrefixes($prefixes, $recursive = true)
  {
    $this->ignorePrefixes = (array) $prefixes;
    $this->ignorePrefixesRecursive = $recursive;

    return $this;
  }

  /**
   * Ignores directories and files by suffixes
   *
   * @param string|array $suffixes A suffix or an array of suffixes
   * @param boolean $recursive When FALSE the suffixes won't be checked in child directories
   * @return rex_dir_iterator The current iterator
   */
  public function ignoreSuffixes($suffixes, $recursive = true)
  {
    $this->ignoreSuffixes = (array) $suffixes;
    $this->ignoreSuffixesRecursive = $recursive;

    return $this;
  }

  /**
  * Ignores system stuff (like .DS_Store, .svn, .git etc.)
  *
  * @return rex_dir_iterator The current iterator
  */
  public function ignoreSystemStuff()
  {
    $this->ignoreSystemStuff = true;

    return $this;
  }

  /**
   * Sorts the elements
   *
   * @param int|callable $sort Sort mode, see {@link rex_sortable_iterator::__construct()}
   * @return rex_sortable_iterator Sortable iterator
   */
  public function sort($sort = rex_sortable_iterator::KEYS)
  {
    return new rex_sortable_iterator($this, $sort);
  }

  /* (non-PHPdoc)
   * @see RecursiveFilterIterator::getChildren()
   */
  public function getChildren()
  {
    /* @var $iterator self */
    $iterator = parent::getChildren();

    if($this->ignoreDirsRecursive)
    {
      $iterator->ignoreDirs($this->ignoreDirs, true);
    }
    if($this->ignoreFilesRecursive)
    {
      $iterator->ignoreFiles($this->ignoreFiles, true);
    }
    if($this->ignorePrefixesRecursive)
    {
      $iterator->ignorePrefixes($this->ignorePrefixes, true);
    }
    if($this->ignoreSuffixesRecursive)
    {
      $iterator->ignoreSuffixes($this->ignoreSuffixes, true);
    }
    $iterator->ignoreSystemStuff = $this->ignoreSystemStuff;

    return $iterator;
  }

  /* (non-PHPdoc)
   * @see RecursiveFilterIterator::accept()
   */
  public function accept()
  {
    /* @var $current SplFileInfo */
    $current = parent::current();
    $filename = $current->getFilename();

    if($current->isDir())
    {
      if($this->ignoreDirs == self::ALL)
      {
        return false;
      }
      if(in_array($filename, $this->ignoreDirs))
      {
        return false;
      }
    }

    if($current->isFile())
    {
      if($this->ignoreFiles == self::ALL)
      {
        return false;
      }
      if(in_array($filename, $this->ignoreFiles))
      {
        return false;
      }
    }

    foreach($this->ignorePrefixes as $prefix)
    {
      if(strpos($filename, $prefix) === 0)
      {
        return false;
      }
    }

    foreach($this->ignoreSuffixes as $suffix)
    {
      if(substr($filename, strlen($filename) - strlen($suffix)) == $suffix)
      {
        return false;
      }
    }

    if($this->ignoreSystemStuff)
    {
      foreach(self::$systemStuff as $systemStuff)
      {
        if(stripos($filename, $systemStuff) === 0)
        {
          return false;
        }
      }
    }

    return true;
  }
}

/**
 * RecursiveIteratorIterator to iterate over a {@link rex_dir_iterator}
 *
 * Unknown method calls will be passed to the inner iterator.
 *
 * @author gharlan
 */
class rex_dir_recursive_iterator extends RecursiveIteratorIterator
{
  /**
   * Sorts the elements
   *
   * @param int|callable $sort Sort mode, see {@link rex_sortable_iterator::__construct()}
   * @return rex_sortable_iterator Sortable iterator
   */
  public function sort($sort = rex_sortable_iterator::KEYS)
  {
    return new rex_sortable_iterator($this, $sort);
  }

  public function __call($method, $arguments)
  {
    call_user_func_array(array($this->getInnerIterator(), $method), $arguments);

    return $this;
  }
}