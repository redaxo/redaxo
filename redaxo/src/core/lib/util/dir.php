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
    if(is_dir($dir))
      return true;

    $parent = dirname($dir);
    if(!is_dir($parent) && (!$recursive || !self::create($parent)))
      return false;

    // file_exists($parent .'/.') checks if the parent directory has the executable permission
    // is_executable($directory) does not work on all systems
    if(is_writable($parent) && file_exists($parent .'/.') && mkdir($dir, rex::getDirPerm()))
    {
      @chmod($dir, rex::getDirPerm());
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
    $srcdir = rtrim($srcdir, DIRECTORY_SEPARATOR);
    $dstdir = rtrim($dstdir, DIRECTORY_SEPARATOR);

    if(!self::create($dstdir))
    {
      return false;
    }

    $state = TRUE;

    foreach(self::recursiveIterator($srcdir, rex_dir_recursive_iterator::SELF_FIRST) as $srcfilepath => $srcfile)
    {
      $dstfile = $dstdir . substr($srcfilepath, strlen($srcdir));
      if($srcfile->isDir())
      {
        $state = self::create($dstfile) && $state;
      }
      elseif(!file_exists($dstfile) || $srcfile->getMTime() > filemtime($dstfile))
      {
        $state = rex_file::copy($srcfilepath, $dstfile) && $state;
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
    return !is_dir($dir) || self::deleteIterator(self::recursiveIterator($dir)) && (!$deleteSelf || rmdir($dir));
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
    return self::deleteIterator($iterator, false);
  }

  /**
   * Deletes files and directories by a rex_dir_iterator
   *
   * @param Traversable $iterator Iterator, $iterator->current() must return a SplFileInfo-Object
   * @param boolean $deleteDirs When FALSE, directories won't be deleted
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function deleteIterator(Traversable $iterator, $deleteDirs = true)
  {
    $state = true;

    foreach($iterator as $file)
    {
      if($file->isDir())
      {
        $state = (!$deleteDirs || rmdir($file)) && $state;
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