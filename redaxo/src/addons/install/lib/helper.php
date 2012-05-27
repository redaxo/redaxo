<?php

class rex_install_helper
{
  static public function copyDirToArchive($dir, $archive, $basename = null, $excludeDirs = null)
  {
    $dir = rtrim($dir, '/\\');
    $basename = $basename ?: basename($dir);
    rex_dir::create(dirname($archive));
    $phar = new PharData($archive, 0, null, Phar::ZIP);
    $files = array();
    $iterator = rex_dir::recursiveIterator($dir, rex_dir_recursive_iterator::LEAVES_ONLY)->ignoreSystemStuff();
    if ($excludeDirs) {
      $iterator->ignoreDirs($excludeDirs, false);
    }
    foreach ($iterator as $path => $file) {
      $files[str_replace($dir, $basename, $path)] = $path;
    }
    $phar->buildFromIterator(new ArrayIterator($files));
    $phar->compressFiles(Phar::GZ);
    foreach ($files as $path => $realpath) {
      if (filesize($realpath) == 0) {
        $phar[$path]->decompress();
      }
    }
  }
}
