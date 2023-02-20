<?php

/**
 * Finder.
 *
 * @author staabm
 * @author gharlan
 *
 * @implements IteratorAggregate<string, SplFileInfo>
 *
 * @package redaxo\core
 */
class rex_finder implements IteratorAggregate, Countable
{
    use rex_factory_trait;

    public const ALL = '__ALL__';

    /** @var string */
    private $dir;
    /** @var bool */
    private $recursive = false;
    /** @var RecursiveIteratorIterator::SELF_FIRST|RecursiveIteratorIterator::CHILD_FIRST|RecursiveIteratorIterator::LEAVES_ONLY */
    private $recursiveMode = RecursiveIteratorIterator::SELF_FIRST;
    /** @var bool */
    private $dirsOnly = false;
    /** @var string[] */
    private $ignoreFiles = [];
    /** @var string[] */
    private $ignoreFilesRecursive = [];
    /** @var string[] */
    private $ignoreDirs = [];
    /** @var string[] */
    private $ignoreDirsRecursive = [];
    /** @var bool */
    private $ignoreSystemStuff = true;
    /** @var false|rex_sortable_iterator::*|callable(mixed, mixed): int */
    private $sort = false;

    /**
     * Contructor.
     *
     * @param string $dir
     */
    private function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Returns a new finder object.
     *
     * @param string $dir Path to a directory
     *
     * @throws InvalidArgumentException
     *
     * @return static
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
     * Activate/Deactivate recursive directory scanning.
     *
     * @param bool $recursive
     *
     * @return $this
     */
    public function recursive($recursive = true)
    {
        $this->recursive = $recursive;

        return $this;
    }

    /**
     * Fetch directory contents before recurse its subdirectories.
     *
     * @return $this
     */
    public function selfFirst()
    {
        $this->recursiveMode = RecursiveIteratorIterator::SELF_FIRST;

        return $this;
    }

    /**
     * Fetch child directories before their parent directory.
     *
     * @return $this
     */
    public function childFirst()
    {
        $this->recursiveMode = RecursiveIteratorIterator::CHILD_FIRST;

        return $this;
    }

    /**
     * Fetch files only.
     *
     * @return $this
     */
    public function filesOnly()
    {
        $this->recursiveMode = RecursiveIteratorIterator::LEAVES_ONLY;

        return $this;
    }

    /**
     * Fetch dirs only.
     *
     * @return $this
     */
    public function dirsOnly()
    {
        $this->dirsOnly = true;

        return $this;
    }

    /**
     * Ignore all files which match the given glob pattern.
     *
     * @param string|array $glob      Glob pattern or an array of glob patterns
     * @param bool         $recursive When FALSE the patterns won't be checked in child directories
     *
     * @return $this
     */
    public function ignoreFiles($glob, $recursive = true)
    {
        $var = $recursive ? 'ignoreFilesRecursive' : 'ignoreFiles';
        if (is_array($glob)) {
            $this->$var += $glob;
        } else {
            $this->$var[] = $glob;
        }

        return $this;
    }

    /**
     * Ignore all directories which match the given glob pattern.
     *
     * @param string|array $glob      Glob pattern or an array of glob patterns
     * @param bool         $recursive When FALSE the patterns won't be checked in child directories
     *
     * @return $this
     */
    public function ignoreDirs($glob, $recursive = true)
    {
        $var = $recursive ? 'ignoreDirsRecursive' : 'ignoreDirs';
        if (is_array($glob)) {
            $this->$var += $glob;
        } else {
            $this->$var[] = $glob;
        }

        return $this;
    }

    /**
     * Ignores system stuff (like .DS_Store, .svn, .git etc.).
     *
     * @param bool $ignoreSystemStuff
     *
     * @return $this
     */
    public function ignoreSystemStuff($ignoreSystemStuff = true)
    {
        $this->ignoreSystemStuff = $ignoreSystemStuff;

        return $this;
    }

    /**
     * Sorts the elements.
     *
     * @param rex_sortable_iterator::*|callable(mixed, mixed): int $sort Sort mode, see {@link rex_sortable_iterator::__construct()}
     *
     * @return $this
     */
    public function sort($sort = rex_sortable_iterator::KEYS)
    {
        $this->sort = $sort;

        return $this;
    }

    /** @return Traversable<string, SplFileInfo> */
    #[ReturnTypeWillChange]
    public function getIterator()
    {
        /** @var RecursiveIterator<string, SplFileInfo> $iterator */
        $iterator = new RecursiveDirectoryIterator($this->dir, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS);

        $iterator = new RecursiveCallbackFilterIterator($iterator, function (SplFileInfo $current, $key, $currentIterator) use ($iterator) {
            $filename = $current->getFilename();
            $isRoot = $currentIterator === $iterator;

            $match = static function ($pattern, $filename) {
                $regex = '/^'.strtr(preg_quote($pattern, '/'), ['\*' => '.*', '\?' => '.']).'$/i';
                return preg_match($regex, $filename);
            };

            if ($current->isFile()) {
                if ($this->dirsOnly) {
                    return false;
                }
                $ignoreFiles = $isRoot ? array_merge($this->ignoreFiles, $this->ignoreFilesRecursive) : $this->ignoreFilesRecursive;
                foreach ($ignoreFiles as $ignore) {
                    if ($match($ignore, $filename)) {
                        return false;
                    }
                }
            }

            if ($current->isDir()) {
                if (!$this->recursive && RecursiveIteratorIterator::LEAVES_ONLY === $this->recursiveMode) {
                    return false;
                }
                $ignoreDirs = $isRoot ? array_merge($this->ignoreDirs, $this->ignoreDirsRecursive) : $this->ignoreDirsRecursive;
                foreach ($ignoreDirs as $ignore) {
                    if ($match($ignore, $filename)) {
                        return false;
                    }
                }
            }

            if ($this->ignoreSystemStuff) {
                foreach (['.DS_Store', 'Thumbs.db', 'desktop.ini', '.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg'] as $systemStuffFile) {
                    if (0 === stripos($filename, $systemStuffFile)) {
                        return false;
                    }
                }
            }

            return true;
        });

        if ($this->recursive) {
            /** @var Traversable<string, SplFileInfo> */
            $iterator = new RecursiveIteratorIterator($iterator, $this->recursiveMode);
        }

        if ($this->sort) {
            $iterator = new rex_sortable_iterator($iterator, $this->sort);
        }

        return $iterator;
    }

    #[ReturnTypeWillChange]
    public function count()
    {
        return iterator_count($this->getIterator());
    }
}
