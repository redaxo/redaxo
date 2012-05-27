<?php

class rex_finder extends rex_factory_base implements IteratorAggregate, Countable
{
  private $baseDir;

  private $recursive;
  private $recursiveMode;

  private $sort;

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

    $this->sort = false;
  }

  /**
   * Use this factory method to allow notations like rex_finder::factory('/my-path/...')->recursive()->filterFiles().. because new rex_finder()->myMethod is only allowed in PHP5.4+
   *
   * @param string $baseDir
   *
   * @return self
   */
  static public function factory($baseDir)
  {
    if(!is_dir($baseDir))
    {
      throw new rex_exception('folder "'. $baseDir .'" not found!');
    }

    $class = static::getFactoryClass();
    return new $class($baseDir);
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

  /**
   * sort the result
   *
   * @param integer|callback $sort     The sort type (rex_finder_sorter::SORT_BY_NAME, rex_finder_sorter::SORT_BY_TYPE, ... or a PHP callback)
   */
  public function sort($sort) {
    $this->sort = $sort;

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

    if($this->sort)
    {
      $iterator = new rex_finder_sorter($iterator, $this->sort);
    }

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

// private utility class, taken from the symfony project
class rex_finder_sorter implements IteratorAggregate {
  const SORT_BY_NAME = 1;
  const SORT_BY_TYPE = 2;
  const SORT_BY_ACCESSED_TIME = 3;
  const SORT_BY_CHANGED_TIME = 4;
  const SORT_BY_MODIFIED_TIME = 5;

  private $iterator;
  private $sort;

  /**
   * Constructor.
   *
   * @param Traversable     $iterator The Iterator to filter
   * @param integer|callback $sort     The sort type (SORT_BY_NAME, SORT_BY_TYPE, or a PHP callback)
   */
  public function __construct(Traversable $iterator, $sort)
  {
    $this->iterator = $iterator;

    if (self::SORT_BY_NAME === $sort) {
      $this->sort = function ($a, $b) {
        return strcmp($a->getRealpath(), $b->getRealpath());
      };
    } elseif (self::SORT_BY_TYPE === $sort) {
      $this->sort = function ($a, $b) {
        if ($a->isDir() && $b->isFile()) {
          return -1;
        } elseif ($a->isFile() && $b->isDir()) {
          return 1;
        }

        return strcmp($a->getRealpath(), $b->getRealpath());
      };
    } elseif (self::SORT_BY_ACCESSED_TIME === $sort) {
      $this->sort = function ($a, $b) {
        return ($a->getATime() > $b->getATime());
      };
    } elseif (self::SORT_BY_CHANGED_TIME === $sort) {
      $this->sort = function ($a, $b) {
        return ($a->getCTime() > $b->getCTime());
      };
    } elseif (self::SORT_BY_MODIFIED_TIME === $sort) {
      $this->sort = function ($a, $b) {
        return ($a->getMTime() > $b->getMTime());
      };
    } elseif (is_callable($sort)) {
      $this->sort = $sort;
    } else {
      throw new rex_exception('The SortableIterator takes a PHP callback or a valid built-in sort algorithm as an argument.');
    }
  }

  public function getIterator()
  {
    $array = iterator_to_array($this->iterator, true);
    uasort($array, $this->sort);

    return new \ArrayIterator($array);
  }
}