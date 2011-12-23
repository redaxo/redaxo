<?php

class rex_install_helper
{
  static public function copyDirToArchive($dir, $archive, $basename = null)
  {
    $basename = $basename ?: basename($dir);
    $archive = new PharData($archive, 0, null, Phar::ZIP);
    $archive->addEmptyDir($basename);
    $iterator = rex_dir::recursiveIterator($dir, rex_dir_recursive_iterator::SELF_FIRST);
    $iterator->excludeVersionControl();
    $iterator->excludeTemporaryFiles();
    foreach($iterator as $path => $file)
    {
      $path = $basename . DIRECTORY_SEPARATOR . str_replace($dir, '', $path);
      if($file->isDir())
      {
        $archive->addEmptyDir($path);
      }
      else
      {
        $archive->addFile($file->getRealPath(), $path);
      }
    }
    $archive->compressFiles(Phar::GZ);
  }
}