<?php

class rex_install_helper
{
  static public function copyDirToArchive($dir, $archive, $basename = null)
  {
    $archive = new PharData($archive, 0, null, Phar::ZIP);
    $iterator = rex_dir::recursiveIterator($dir);
    $iterator->excludeVersionControl();
    $iterator->excludeTemporaryFiles();
    if($basename)
    {
      $array = array();
      foreach($iterator as $path => $file)
      {
        $array[$basename .'/'. str_replace($dir, '', $path)] = $path;
      }
      $archive->buildFromIterator(new ArrayIterator($array));
    }
    else
    {
      $archive->buildFromIterator($iterator, dirname($dir));
    }
    $archive->compressFiles(Phar::GZ);
  }
}