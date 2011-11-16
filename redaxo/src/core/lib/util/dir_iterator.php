<?php

/**
 * Directory iterator
 *
 * @author gharlan
 */
class rex_dir_iterator extends RecursiveFilterIterator
{
  const ALL = 'EXCLUDE_ALL';

  private
    $excludeDirs = array(),
    $excludeDirsRecursive = true,
    $excludeFiles = array(),
    $excludeFilesRecursive = true,
    $excludePrefixes = array(),
    $excludePrefixesRecursive = true,
    $excludeSuffixes = array(),
    $excludeSuffixesRecursive = true;

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
   * Excludes directories
   *
   * @param string|array $dirnames Directory name or an array of directory names
   * @param boolean $recursive When FALSE the dirnames won't be checked in child directories
   * @return rex_dir_iterator The current iterator
   */
  public function excludeDirs($dirnames = self::ALL, $recursive = true)
  {
    $this->excludeDirs = $dirnames == self::ALL ? self::ALL : (array) $dirnames;
    $this->excludeDirsRecursive = $recursive;

    return $this;
  }

  /**
   * Excludes files
   *
   * @param string|array $filenames Filename or an array of filenames
   * @param boolean $recursive When FALSE the filenames won't be checked in child directories
   * @return rex_dir_iterator The current iterator
   */
  public function excludeFiles($filenames = self::ALL, $recursive = true)
  {
    $this->excludeFiles = $filenames == self::ALL ? self::ALL : (array) $filenames;
    $this->excludeFilesRecursive = $recursive;

    return $this;
  }

  /**
   * Excludes directories and files by prefixes
   *
   * @param string|array $prefixes A prefix or an array of prefixes
   * @param boolean $recursive When FALSE the prefixes won't be checked in child directories
   * @return rex_dir_iterator The current iterator
   */
  public function excludePrefixes($prefixes, $recursive = true)
  {
    $this->excludePrefixes = (array) $prefixes;
    $this->excludePrefixesRecursive = $recursive;

    return $this;
  }

  /**
   * Excludes directories and files by suffixes
   *
   * @param string|array $suffixes A suffix or an array of suffixes
   * @param boolean $recursive When FALSE the suffixes won't be checked in child directories
   * @return rex_dir_iterator The current iterator
   */
  public function excludeSuffixes($suffixes, $recursive = true)
  {
    $this->excludeSuffixes = (array) $suffixes;
    $this->excludeSuffixesRecursive = $recursive;

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

    if($this->excludeDirsRecursive)
    {
      $iterator->excludeDirs($this->excludeDirs, true);
    }
    if($this->excludeFilesRecursive)
    {
      $iterator->excludeFiles($this->excludeFiles, true);
    }
    if($this->excludePrefixesRecursive)
    {
      $iterator->excludePrefixes($this->excludePrefixes, true);
    }
    if($this->excludeSuffixesRecursive)
    {
      $iterator->excludeSuffixes($this->excludeSuffixes, true);
    }

    return $iterator;
  }

  /* (non-PHPdoc)
   * @see RecursiveFilterIterator::accept()
   */
  public function accept()
  {
    /* @var $current SplFileInfo */
    $current = parent::current();

    if($current->isDir())
    {
      if($this->excludeDirs == self::ALL)
      {
        return false;
      }
      if(in_array($current->getFilename(), $this->excludeDirs))
      {
        return false;
      }
    }

    if($current->isFile())
    {
      if($this->excludeFiles == self::ALL)
      {
        return false;
      }
      if(in_array($current->getFilename(), $this->excludeFiles))
      {
        return false;
      }
    }

    foreach($this->excludePrefixes as $prefix)
    {
      if(strpos($current->getFilename(), $prefix) === 0)
      {
        return false;
      }
    }

    foreach($this->excludeSuffixes as $suffix)
    {
      if(substr($current->getFilename(), strlen($current->getFilename()) - strlen($suffix)) == $suffix)
      {
        return false;
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