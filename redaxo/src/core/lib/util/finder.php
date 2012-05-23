<?php

class rex_finder implements IteratorAggregate
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
    $this->recursiveMode = RecursiveIteratorIterator::CHILD_FIRST;
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
  }

  /**
   * @return self
   */
  public function childFirst()
  {
    $this->recursiveMode = RecursiveIteratorIterator::CHILD_FIRST;
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
    static $i = 0;
    $i++;

    $iterator = new rex_finder_filter( new RecursiveDirectoryIterator( $this->baseDir, FilesystemIterator::CURRENT_AS_FILEINFO & FilesystemIterator::SKIP_DOTS));
    $iterator->debug = $i == 3;
    $iterator->filterDirs = $this->filterDirs;
    $iterator->filterFiles = $this->filterFiles;
    $iterator->ignoreDirs = $this->ignoreDirs;
    $iterator->ignoreFiles = $this->ignoreFiles;
    $iterator->ignoreSystemStuff = $this->ignoreSystemStuff;

    if ($this->recursive)
    {
      $iterator = new RecursiveIteratorIterator($iterator, $this->recursiveMode);
    }

    return $iterator;
  }
}

// private utility class
class rex_finder_filter extends RecursiveFilterIterator
{
  public $debug = false;

  public $filterFiles = array();
  public $filterDirs = array();

  public $ignoreFiles = array();
  public $ignoreDirs = array();

  public $ignoreSystemStuff = true;

  static private
    $systemStuff = array('.DS_Store', 'Thumbs.db', 'desktop.ini', '.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg');

//   public function filter($glob)
//   {
//     $this->filters[] = $glob;
//   }

//   public function ignore($glob)
//   {
//     $this->ignores[] = $glob;
//   }

//   public function ignoreSystemStuff($ignore)
//   {
//     $this->ignoreSystemStuff = $ignore;
//   }

  public function accept()
  {
    /* @var $current SplFileInfo */
    $current = parent::current();

    $filename = $current->getFilename();
    if($this->debug) echo $filename." ";


//     if($this->recursive && $this->hasChildren())
//     {
//       $this->debug && var_dump("recursive-match");
//       return true;
//     }

    // check the whitelist
    $filters = $current->isDir() ? $this->filterDirs : $this->filterFiles;
    foreach($filters as $filter)
    {
      if(fnmatch($filter, $filename))
      {
        $this->debug && var_dump("matched");
        return true;
      }
    }

    // check for system ignore
    if($this->ignoreSystemStuff)
    {
      foreach(self::$systemStuff as $systemStuff)
      {
        if(stripos($filename, $systemStuff) === 0)
        {
          $this->debug && var_dump("sysstuff");
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
        $this->debug && var_dump("ignored");
        return false;
      }
    }


    // in case ignores are present, everything despite the ignores is accepted, otherwise declined.
    $this->debug && var_dump(empty($this->ignoreDirs) && empty($this->ignoreFiles) ? "accepted" : "declined");
    return empty($this->ignoreDirs) && empty($this->ignoreFiles);
  }
}
