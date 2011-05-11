<?php

/**
 * Class for handling directories
 */
class rex_dir
{
  /**
   * Creates a directory
   *
   * @param string $dir Path of the new directory
   * @param boolean $recursive When FALSE, nested directories won't be created
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function create($dir, $recursive = true)
  {
    global $REX;

    if(is_dir($dir) || mkdir($dir, $REX['DIRPERM'], $recursive))
    {
      chmod($dir, $REX['DIRPERM']);
      return true;
    }

    return false;
  }

  /**
   * Copies a directory
   *
   * @param string $srcdir Path of the source directory
   * @param string $dstdir Path of the destination directory
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function copy($srcdir, $dstdir)
  {
    global $REX;

    $state = TRUE;

    $srcdir = rtrim($srcdir, DIRECTORY_SEPARATOR);
    $dstdir = rtrim($dstdir, DIRECTORY_SEPARATOR);

    if(!is_dir($dstdir))
    {
      self::create($dstdir);
    }

    if($curdir = opendir($srcdir))
    {
      while($file = readdir($curdir))
      {
        if($file != '.' && $file != '..' && $file != '.svn')
        {
          $srcfile = $srcdir . DIRECTORY_SEPARATOR . $file;
          $dstfile = $dstdir . DIRECTORY_SEPARATOR . $file;
          if(is_file($srcfile))
          {
            if(!file_exists($dstfile) || (filemtime($srcfile) - filemtime($dstfile)) > 0)
            {
              $state = rex_file::copy($srcfile, $dstfile) && $state;
            }
          }
          else if(is_dir($srcfile))
          {
            $state = self::copy($srcfile, $dstfile) && $state;
          }
        }
      }
      closedir($curdir);
    }
    return $state;
  }

  /**
   * Deletes a directory
   *
   * @param string $dir Path of the directory
   * @param boolean $deleteSelf When FALSE, only subdirectories and files will be deleted
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function delete($dir, $deleteSelf = true)
  {
    return self::_delete($dir, true, true, $deleteSelf);
  }

  /**
   * Deletes the files in a directory
   *
   * @param string $dir Path of the directory
   * @param boolean $recursive When FALSE, files in subdirectories won't be deleted
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function deleteFiles($dir, $recursive = true)
  {
    return self::_delete($dir, $recursive, false, false);
  }

  /**
   * Deletes a directory
   *
   * @param string $dir Path of the directory
   * @param boolean $recursive When FALSE, files in subdirectories won't be deleted
   * @param boolean $deleteDirs When FALSE, only files will be deleted
   * @param boolean $deleteSelf When FALSE, only subdirectories and files will be deleted
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  static private function _delete($dir, $recursive = true, $deleteDirs = true, $deleteSelf = true)
  {
    $state = TRUE;

    $dir = rtrim($dir, DIRECTORY_SEPARATOR);

    if (file_exists($dir) && ($handle = opendir($dir)))
    {
      while ($filename = readdir($handle))
      {
        if ($filename != '.' && $filename != '..')
        {
          $file = $dir . DIRECTORY_SEPARATOR . $filename;
          if(is_file($file))
          {
            $state = rex_file::delete($file) && $state;
          }
          else if($recursive)
          {
            $state = self::_delete($file, $recursive, $deleteDirs) && $state;
          }
        }
      }
      closedir($handle);

      if ($state !== TRUE || ($deleteSelf && $deleteDirs && !rmdir($dir)))
      {
        return FALSE;
      }
    }
    else
    {
      return FALSE;
    }

    return TRUE;
  }
}