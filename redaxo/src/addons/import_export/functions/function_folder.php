<?php

/**
 * @package redaxo\import-export
 */

/**
 * Returns the content of the given folder
 *
 * @param string $dir Path to the folder
 * @return array Content of the folder or false on error
 * @author Markus Staab <staab@public-4u.de>
 */
if (!function_exists('readFolder')) {
     function readFolder($dir)
     {
            if (!is_dir($dir)) {
                throw new rex_exception('Folder "' . $dir . '" is not available or not a directory');
            }
            $hdl = opendir($dir);
            $folder = [];
            while (false !== ($file = readdir($hdl))) {
                 $folder[] = $file;
            }

            return $folder;
     }
}

/**
 * Returns the content of the given folder.
 * The content will be filtered with the given $fileprefix
 *
 * @param string $dir        Path to the folder
 * @param string $fileprefix Fileprefix to filter
 * @return array Filtered-content of the folder or false on error
 * @author Markus Staab <staab@public-4u.de>
 */

if (!function_exists('readFilteredFolder')) {
     function readFilteredFolder($dir, $fileprefix)
     {
            $filtered = [];
            $folder = readFolder($dir);

            if (!$folder) {
                 return false;
            }

            foreach ($folder as $file) {
                 if (substr($file, strlen($file) - strlen($fileprefix)) == $fileprefix) {
                        $filtered[] = $file;
                 }
            }

            return $filtered;
     }
}

/**
 * Returns the files of the given folder
 *
 * @param string $dir Path to the folder
 * @return array Files of the folder or false on error
 * @author Markus Staab <staab@public-4u.de>
 */
if (!function_exists('readFolderFiles')) {
     function readFolderFiles($dir)
     {
            $folder = readFolder($dir);
            $files = [];

            if (!$folder) {
                 return false;
            }

            foreach ($folder as $file) {
                 if (is_file($dir . '/' . $file)) {
                        $files[] = $file;
                 }
            }

            return $files;
     }
}

/**
 * Returns the subfolders of the given folder
 *
 * @param string $dir         Path to the folder
 * @param bool   $ignore_dots True if the system-folders "." and ".." should be ignored
 * @return array Subfolders of the folder or false on error
 * @author Markus Staab <staab@public-4u.de>
 */
if (!function_exists('readSubFolders')) {
     function readSubFolders($dir, $ignore_dots = true)
     {
            $folder = readFolder($dir);
            $folders = [];

            if (!$folder) {
                 return false;
            }

            foreach ($folder as $file) {
                 if ($ignore_dots && ($file == '.' || $file == '..')) {
                        continue;
                 }
                 if (is_dir($dir . '/' . $file)) {
                        $folders[] = $file;
                 }
            }

            return $folders;
     }
}
