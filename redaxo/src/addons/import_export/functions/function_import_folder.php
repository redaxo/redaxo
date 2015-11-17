<?php

/**
 * @package redaxo\import-export
 */

// Import Folder Functions
function getImportDir()
{
    $dir = rex_path::addonData('import_export', 'backups');
    rex_dir::create($dir);

    return $dir;
}

function readImportFolder($fileprefix)
{
    $folder = readFilteredFolder(getImportDir(), $fileprefix);

    usort($folder, 'compareFiles');

    return $folder;
}

function compareFiles($file_a, $file_b)
{
    $dir = getImportDir();

    $time_a = filemtime($dir . '/' . $file_a);
    $time_b = filemtime($dir . '/' . $file_b);

    if ($time_a == $time_b) {
        return 0;
    }

    return ($time_a > $time_b) ? -1 : 1;
}
