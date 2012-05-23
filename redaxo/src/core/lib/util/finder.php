<?php

class rex_finder implements IteratorAggregate
{
  private $baseDir;
  private $recursive;

  private $filters = array();
  private $ignores = array();
  private $ignoreSystemStuff = true;

  private function __construct($baseDir)
  {
    $this->baseDir = $baseDir;
  }

  /**
   * Use this factory method to allow notations like rex_finder::create()->recursive()->filter().. because new rex_finder()->myMethod is only allowed in PHP5.4+
   *
   * @param string $baseDir
   *
   * @return self
   */
  static public function create($baseDir)
  {
    if(!is_dir($baseDir))
    {
      throw new rex_exception('folder "'. $baseDir .'" not found!');
    }

    $finder = new rex_finder($baseDir);
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
   * @param string $glob
   *
   * @return self
   */
  public function filter($glob)
  {
    $this->filters[] = $glob;

    return $this;
  }

  /**
   * @param string $glob
   *
   * @return self
   */
  public function ignore($glob)
  {
    $this->ignores[] = $glob;

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
    $iterator->filters = $this->filters;
    $iterator->ignores = $this->ignores;
    $iterator->ignoreSystemStuff = $this->ignoreSystemStuff;

    if ($this->recursive)
    {
      $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
    }

    return $iterator;
  }
}

// private utility class
class rex_finder_filter extends RecursiveFilterIterator
{
  public $debug = false;

  public $filters = array();
  public $ignores = array();

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

    if($current->isDir()) return true;

    // check the whitelist
    foreach($this->filters as $filter)
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
    foreach($this->ignores as $ignore)
    {
      if(fnmatch($ignore, $filename))
      {
        $this->debug && var_dump("ignored");
        return false;
      }
    }

    $this->debug && var_dump(empty($this->filters) ? "accepted" : "declined");
    return empty($this->filters);
  }

  /* (non-PHPdoc)
   * @see RecursiveFilterIterator::getChildren()
   */
  public function getChildren()
  {
//     if(!$this->recursive)
//     {
//       echo "noot recursive";
//       return new RecursiveFilterIterator(new RecursiveArrayIterator(array()));
//     }

//     /* @var $iterator self */
    $this->debug && var_dump("getchildren");

    $iterator = parent::getChildren();
//     echo "reeturn children ". count($iterator);
    return $iterator;
  }
}
