<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_set_rights
{
    public static function setRights($folder)
    {
        @chmod($folder, rex::getDirPerm());
        $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder), RecursiveIteratorIterator::CHILD_FIRST);
        foreach($dir as $dstFile)
        {
            $fileName = $dstFile->getFilename();
            if ($fileName == '.' || $fileName == '..') {
                continue;
          } else {
              if (is_dir($dstFile->getPathname())) {                
                  @chmod($dstFile->getPathname(), rex::getDirPerm());
              } else {
                  @chmod($dstFile->getPathname(), rex::getFilePerm());
              }
          }
        }
    }
}
