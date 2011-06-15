<?php

/**
 * Class for handling directories
 *
 * @author gharlan
 */
class rex_dir
{
  /**
   * Creates a directory
   *
   * @param string $dir Path of the new directory
   * @param boolean $recursive When FALSE, nested directories won't be created
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function create($dir, $recursive = true)
  {
    if(is_dir($dir) || mkdir($dir, rex::getDirPerm(), $recursive))
    {
      chmod($dir, rex::getDirPerm());
      return true;
    }

    return false;
  }

  /**
   * Copies a directory
   *
   * @param string $srcdir Path of the source directory
   * @param string $dstdir Path of the destination directory
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function copy($srcdir, $dstdir)
  {
    $state = TRUE;

    $srcdir = rtrim($srcdir, DIRECTORY_SEPARATOR);
    $dstdir = rtrim($dstdir, DIRECTORY_SEPARATOR);

    self::create($dstdir);

    foreach(self::recursiveIterator($srcdir) as $srcfile)
    {
      $dstfile = $dstdir . substr($srcfile->getRealPath(), strlen($srcdir));
      if($srcfile->isDir())
      {
        $state = self::create($dstfile) && $state;
      }
      elseif(!file_exists($dstfile) || $srcfile->getMTime() > filemtime($dstfile))
      {
        $state = rex_file::copy($srcfile->getRealPath(), $dstfile) && $state;
      }
    }

    return $state;
  }

  /**
   * Deletes a directory
   *
   * @param string $dir Path of the directory
   * @param boolean $deleteSelf When FALSE, only subdirectories and files will be deleted
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function delete($dir, $deleteSelf = true)
  {
    if(self::deleteIterator(self::recursiveIterator($dir)))
    {
      if(!$deleteSelf || rmdir($dir))
      {
        return true;
      }
    }
    return false;
  }

  /**
   * Deletes the files in a directory
   *
   * @param string $dir Path of the directory
   * @param boolean $recursive When FALSE, files in subdirectories won't be deleted
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function deleteFiles($dir, $recursive = true)
  {
    $iterator = $recursive ? self::recursiveIterator($dir) : self::iterator($dir);
    return self::deleteIterator($iterator->excludeDirs());
  }

  /**
   * Deletes files and directories by a rex_dir_iterator
   *
   * @param Traversable $iterator Iterator, $iterator->current() must return a SplFileInfo-Object
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function deleteIterator(Traversable $iterator)
  {
    $state = true;

    foreach($iterator as $file)
    {
      if($file->isDir())
      {
        $state = rmdir($file) && $state;
      }
      else
      {
        $state = rex_file::delete($file) && $state;
      }
    }

    return $state;
  }

  /**
   * Returns an iterator for a directory
   *
   * @param string $dir Path of the directory
   * @return rex_dir_iterator
   * @see rex_dir_iterator
   */
  static public function iterator($dir)
  {
    return new rex_dir_iterator(new RecursiveDirectoryIterator($dir));
  }

  /**
   * Returns a recursive iterator for a directory
   *
   * @param string $dir Path of the directory
   * @param int $mode Mode, see {@link http://www.php.net/manual/en/recursiveiteratoriterator.construct.php}
   * @return rex_dir_iterator
   * @see rex_dir_iterator
   */
  static public function recursiveIterator($dir, $mode = rex_dir_recursive_iterator::CHILD_FIRST)
  {
    return new rex_dir_recursive_iterator(self::iterator($dir), $mode);
  }
}