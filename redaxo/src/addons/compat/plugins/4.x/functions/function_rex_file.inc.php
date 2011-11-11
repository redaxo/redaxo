<?php

/**
 * @see rex_file::put()
 *
 * @deprecated 5.0
 */
function rex_put_file_contents($path, $content)
{
  return rex_file::put($path, $content);
}

/**
 * @see rex_file::get()
 *
 * @deprecated 5.0
 */
function rex_get_file_contents($path)
{
  return rex_file::get($path);
}

/**
 * @see rex_file::put()
 * @see rex_file::putConfig()
 *
 * @deprecated 5.0
 */
function rex_replace_dynamic_contents($path, $content)
{
  if($fcontent = rex_file::get($path))
  {
    $content = "// --- DYN\n". trim($content) ."\n// --- /DYN";
    $fcontent = preg_replace("@(\/\/.---.DYN.*\/\/.---.\/DYN)@s", $content, $fcontent);
    return rex_file::put($path, $fcontent);
  }
  return false;
}

/**
 * @see rex_dir::delete()
 * @see rex_dir::deleteFiles()
 *
 * @deprecated 5.0
 */
function rex_deleteDir($file, $delete_folders = FALSE)
{
  if($deleteFolders)
    return rex_dir::delete($file);
  else
    return rex_dir::deleteFiles($file);
}

/**
 * @see rex_dir::deleteFiles()
 *
 * @deprecated 5.0
 */
function rex_deleteFiles($file)
{
  return rex_dir::deleteFiles($file);
}

/**
 * @see rex_dir::create()
 *
 * @deprecated 5.0
 */
function rex_createDir($dir, $recursive = true)
{
  return rex_dir::create($dir, $recursive);
}

/**
 * @see rex_dir::copy()
 *
 * @deprecated 5.0
 */
function rex_copyDir($srcdir, $dstdir)
{
  return rex_dir::copy($srcdir, $dstdir);
}

/**
 * @see rex_path::absolute()
 *
 * @deprecated 5.0
 */
function rex_absPath($rel_path, $rel_to_current = false)
{
  return rex_path::absolute($rel_path, $rel_to_current);
}