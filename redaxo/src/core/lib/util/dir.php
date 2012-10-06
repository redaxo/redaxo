<?php

use Symfony\Component\Finder\Finder;

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
   * @param string  $dir       Path of the new directory
   * @param boolean $recursive When FALSE, nested directories won't be created
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function create($dir, $recursive = true)
  {
    if (is_dir($dir))
      return true;

    $parent = dirname($dir);

    if (!is_dir($parent) && (!$recursive || !self::create($parent)))
      return false;

    // file_exists($parent .'/.') checks if the parent directory has the executable permission
    // is_executable($directory) does not work on all systems
    if (is_writable($parent) && file_exists($parent . '/.') && mkdir($dir, rex::getDirPerm())) {
      @chmod($dir, rex::getDirPerm());
      return true;
    }

    return false;
  }

  /**
   * Returns wether the directory is writable
   *
   * @param string $dir Path of the directory
   * @return boolean
   */
  static public function isWritable($dir)
  {
    $dir = rtrim($dir, DIRECTORY_SEPARATOR);
    return @is_dir($dir) && @is_writable($dir . DIRECTORY_SEPARATOR . '.');
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

    if (!is_dir($srcdir)) {
      return false;
    }

    if (!self::create($dstdir)) {
      return false;
    }

    $state = true;

    $finder = Finder::create()
      ->in($srcdir)
    ;

    foreach ($finder as $srcfilepath => $srcfile) {
      $dstfile = $dstdir . substr($srcfilepath, strlen($srcdir));
      if ($srcfile->isDir()) {
        $state = self::create($dstfile) && $state;
      } elseif (!file_exists($dstfile) || $srcfile->getMTime() > filemtime($dstfile)) {
        $state = rex_file::copy($srcfilepath, $dstfile) && $state;
      }
    }

    return $state;
  }

  /**
   * Deletes a directory
   *
   * @param string  $dir        Path of the directory
   * @param boolean $deleteSelf When FALSE, only subdirectories and files will be deleted
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function delete($dir, $deleteSelf = true)
  {
    if (!is_dir($dir)) {
      return false;
    }

    $finder = Finder::create()
      ->in($dir)
    ;

    return self::deleteIterator($finder) && (!$deleteSelf || rmdir($dir));
  }

  /**
   * Deletes the files in a directory
   *
   * @param string  $dir       Path of the directory
   * @param boolean $recursive When FALSE, files in subdirectories won't be deleted
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function deleteFiles($dir, $recursive = true)
  {
    $finder = Finder::create()->in($dir);

    if (!$recursive) {
      $finder->depth(0);
    }

    return self::deleteIterator($finder, false);
  }

  /**
   * Deletes files and directories by a rex_dir_iterator
   *
   * @param Traversable $iterator   Iterator, $iterator->current() must return a -Object
   * @param boolean     $deleteDirs When FALSE, directories won't be deleted
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function deleteIterator(Traversable $iterator, $deleteDirs = true)
  {
    $state = true;

    $files = iterator_to_array($iterator);
    $files = array_reverse($files);

    foreach ($files as $file) {
      if ($file->isDir()) {
        $state = (!$deleteDirs || rmdir($file)) && $state;
      } else {
        $state = rex_file::delete($file) && $state;
      }
    }

    return $state;
  }
}
