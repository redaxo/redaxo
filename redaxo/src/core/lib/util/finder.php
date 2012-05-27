<?php

class rex_finder implements IteratorAggregate, Countable
{
  private $baseDir;

  private $recursive;
  private $recursiveMode;

  private $filterFiles = array();
  private $filterDirs = array();

  private $ignoreFiles = array();
  private $ignoreDirs = array();

  private $ignoreSystemStuff = true;

  private function __construct($baseDir)
  {
    $this->baseDir = $baseDir;

    $this->recursive = false;
    $this->recursiveMode = RecursiveIteratorIterator::SELF_FIRST;
  }

  /**
   * Use this factory method to allow notations like rex_finder::get()->recursive()->filterFiles().. because new rex_finder()->myMethod is only allowed in PHP5.4+
   *
   * @param string $baseDir
   *
   * @return self
   */
  static public function get($baseDir)
  {
    if(!is_dir($baseDir))
    {
      throw new rex_exception('folder "'. $baseDir .'" not found!');
    }

    $finder = new self($baseDir);
    return $finder;
  }

  /**
   * @param boolean $recursive
   *
   * @return self
   */
  public function recursive($recursive = true)
  {
    $this->recursive = $recursive;

    return $this;
  }

  /**
   * @return self
   */
  public function selfFirst()
  {
    $this->recursiveMode = RecursiveIteratorIterator::SELF_FIRST;

    return $this;
  }

  /**
   * @return self
   */
  public function childFirst()
  {
    $this->recursiveMode = RecursiveIteratorIterator::CHILD_FIRST;

    return $this;
  }


  /**
   * @param string $glob
   *
   * @return self
   */
  public function filterFiles($glob)
  {
    $this->filterFiles[] = $glob;

    return $this;
  }

  /**
   * @param string $glob
   *
   * @return self
   */
  public function filterDirs($glob)
  {
    $this->filterDirs[] = $glob;

    return $this;
  }

  /**
   * @param string $glob
   *
   * @return self
   */
  public function ignoreFiles($glob)
  {
    $this->ignoreFiles[] = $glob;

    return $this;
  }

  /**
   * @param string $glob
   *
   * @return self
   */
  public function ignoreDirs($glob)
  {
    $this->ignoreDirs[] = $glob;

    return $this;
  }

  /**
   * @param boolean $ignore
   *
   * @return self
   */
  public function ignoreSystemStuff($ignore=true)
  {
    $this->ignoreSystemStuff = $ignore;

    return $this;
  }

  public function getIterator()
  {
    $iterator = new RecursiveDirectoryIterator( $this->baseDir, FilesystemIterator::CURRENT_AS_FILEINFO & FilesystemIterator::SKIP_DOTS);
    if ($this->recursive)
    {
      $iterator = new RecursiveIteratorIterator($iterator, $this->recursiveMode);
    }
    $iterator = new rex_finder_filter( $iterator );
    $iterator->filterDirs = $this->filterDirs;
    $iterator->filterFiles = $this->filterFiles;
    $iterator->ignoreDirs = $this->ignoreDirs;
    $iterator->ignoreFiles = $this->ignoreFiles;
    $iterator->ignoreSystemStuff = $this->ignoreSystemStuff;

    return $iterator;
  }

  public function count () {
    return iterator_count($this->getIterator());
  }
}

// private utility class
class rex_finder_filter extends FilterIterator
{
  public $filterFiles = array();
  public $filterDirs = array();

  public $ignoreFiles = array();
  public $ignoreDirs = array();

  public $ignoreSystemStuff = true;

  static private
    $systemStuff = array('.DS_Store', 'Thumbs.db', 'desktop.ini', '.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg');

  public function accept()
  {
    /* @var $current SplFileInfo */
    $current = parent::current();
    $filename = $current->getFilename();

    // check for system ignore
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

    // check the blacklist
    $ignores = $current->isDir() ? $this->ignoreDirs : $this->ignoreFiles;
    foreach($ignores as $ignore)
    {
      if(fnmatch($ignore, $filename))
      {
        return false;
      }
    }

    $matched = true;
    // check the whitelist
    $filters = $current->isDir() ? $this->filterDirs : $this->filterFiles;
    if($filters)
    {
      $matched = false;
      foreach($filters as $filter)
      {
        if(fnmatch($filter, $filename))
        {
          return true;
        }
      }
    }

    // in case ignores are present, everything despite the ignores is accepted, otherwise declined.
    return $matched;
  }
}
