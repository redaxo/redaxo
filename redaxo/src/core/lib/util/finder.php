<?php

/**
 * Finder
 *
 * @author staabm
 * @author gharlan
 */
class rex_finder extends rex_factory_base implements IteratorAggregate, Countable
{
    const ALL = '__ALL__';

    private
        $dir,
        $recursive = false,
        $recursiveMode = RecursiveIteratorIterator::SELF_FIRST,
        $dirsOnly = false,
        $ignoreFiles = array(),
        $ignoreFilesRecursive = array(),
        $ignoreDirs = array(),
        $ignoreDirsRecursive = array(),
        $ignoreSystemStuff = true,
        $sort = false;

    /**
     * Contructor
     *
     * @param string $dir
     */
    private function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Returns a new finder object
     *
     * @param string $dir Path to a directory
     * @return self
     */
    public static function factory($dir)
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException('Folder "' . $dir . '" not found!');
        }

        $class = static::getFactoryClass();
        return new $class($dir);
    }

    /**
     * Activate/Deactivate recursive directory scanning
     *
     * @param boolean $recursive
     * @return self
     */
    public function recursive($recursive = true)
    {
        $this->recursive = $recursive;

        return $this;
    }

    /**
     * Fetch directory contents before recurse its subdirectories.
     *
     * @return self
     */
    public function selfFirst()
    {
        $this->recursiveMode = RecursiveIteratorIterator::SELF_FIRST;

        return $this;
    }

    /**
     * Fetch child directories before their parent directory.
     *
     * @return self
     */
    public function childFirst()
    {
        $this->recursiveMode = RecursiveIteratorIterator::CHILD_FIRST;

        return $this;
    }

    /**
     * Fetch files only
     *
     * @return self
     */
    public function filesOnly()
    {
        $this->recursiveMode = RecursiveIteratorIterator::LEAVES_ONLY;

        return $this;
    }

    /**
     * Fetch dirs only
     *
     * @return self
     */
    public function dirsOnly()
    {
        $this->dirsOnly = true;

        return $this;
    }

    /**
     * Ignore all files which match the given glob pattern
     *
     * @param string|array $glob      Glob pattern or an array of glob patterns
     * @param boolean      $recursive When FALSE the patterns won't be checked in child directories
     * @return self
     */
    public function ignoreFiles($glob, $recursive = true)
    {
        $var = $recursive ? 'ignoreFilesRecursive' : 'ignoreFiles';
        if (is_array($glob)) {
            $this->$var += $glob;
        } else {
            array_push($this->$var, $glob);
        }

        return $this;
    }

    /**
     * Ignore all directories which match the given glob pattern
     *
     * @param string|array $glob      Glob pattern or an array of glob patterns
     * @param boolean      $recursive When FALSE the patterns won't be checked in child directories
     * @return self
     */
    public function ignoreDirs($glob, $recursive = true)
    {
        $var = $recursive ? 'ignoreDirsRecursive' : 'ignoreDirs';
        if (is_array($glob)) {
            $this->$var += $glob;
        } else {
            array_push($this->$var, $glob);
        }

        return $this;
    }

    /**
     * Ignores system stuff (like .DS_Store, .svn, .git etc.)
     *
     * @param boolean $ignoreSystemStuff
     * @return self
     */
    public function ignoreSystemStuff($ignoreSystemStuff = true)
    {
        $this->ignoreSystemStuff = $ignoreSystemStuff;

        return $this;
    }

    /**
     * Sorts the elements
     *
     * @param int|callable $sort Sort mode, see {@link rex_sortable_iterator::__construct()}
     * @return self
     */
    public function sort($sort = rex_sortable_iterator::KEYS)
    {
        $this->sort = $sort;

        return $this;
    }

    /* (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        $iterator = new RecursiveDirectoryIterator($this->dir, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS);

        $iterator = new rex_finder_filter($iterator);
        $iterator->dirsOnly = $this->dirsOnly;
        $iterator->ignoreFiles = $this->ignoreFiles;
        $iterator->ignoreFilesRecursive = $this->ignoreFilesRecursive;
        $iterator->ignoreDirs = $this->ignoreDirs;
        $iterator->ignoreDirsRecursive = $this->ignoreDirsRecursive;
        $iterator->ignoreSystemStuff = $this->ignoreSystemStuff;

        if ($this->recursive) {
            $iterator = new RecursiveIteratorIterator($iterator, $this->recursiveMode);
        } elseif ($this->recursiveMode === RecursiveIteratorIterator::LEAVES_ONLY) {
            $iterator->ignoreDirs[] = '*';
        }

        if ($this->sort) {
            $iterator = new rex_sortable_iterator($iterator, $this->sort);
        }

        return $iterator;
    }

    /* (non-PHPdoc)
     * @see Countable::count()
     */
    public function count()
    {
        return iterator_count($this->getIterator());
    }
}

/**
 * Private utility class
 *
 * @author staabm
 * @author gharlan
 */
class rex_finder_filter extends RecursiveFilterIterator
{
    public
        $dirsOnly = false,
        $ignoreFiles = array(),
        $ignoreFilesRecursive = array(),
        $ignoreDirs = array(),
        $ignoreDirsRecursive = array(),
        $ignoreSystemStuff = true;

    private static
        $systemStuff = array('.DS_Store', 'Thumbs.db', 'desktop.ini', '.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg');

    /**
     * Constructor
     *
     * @param RecursiveDirectoryIterator $iterator
     */
    public function __construct(RecursiveDirectoryIterator $iterator)
    {
        parent::__construct($iterator);
    }

    /* (non-PHPdoc)
     * @see RecursiveFilterIterator::getChildren()
     */
    public function getChildren()
    {
        /* @var $iterator self */
        $iterator = parent::getChildren();

        $iterator->dirsOnly = $this->dirsOnly;
        $iterator->ignoreFilesRecursive = $this->ignoreFilesRecursive;
        $iterator->ignoreDirsRecursive = $this->ignoreDirsRecursive;
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

        if ($current->isFile()) {
            if ($this->dirsOnly) {
                return false;
            }
            $ignoreFiles = array_merge($this->ignoreFiles, $this->ignoreFilesRecursive);
            foreach ($ignoreFiles as $ignore) {
                if (fnmatch($ignore, $filename)) {
                    return false;
                }
            }
        }

        if ($current->isDir()) {
            $ignoreDirs = array_merge($this->ignoreDirs, $this->ignoreDirsRecursive);
            foreach ($ignoreDirs as $ignore) {
                if (fnmatch($ignore, $filename)) {
                    return false;
                }
            }
        }

        if ($this->ignoreSystemStuff) {
            foreach (self::$systemStuff as $systemStuff) {
                if (stripos($filename, $systemStuff) === 0) {
                    return false;
                }
            }
        }

        return true;
    }
}
