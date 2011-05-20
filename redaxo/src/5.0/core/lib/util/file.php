<?php

/**
 * Class for handling files
 */
class rex_file
{
  /**
   * Returns the content of a file
   *
   * @param string $file Path to the file
   *
   * @return string Content of the file
   */
  static public function get($file)
  {
    return file_get_contents($file);
  }

  /**
   * Returns the content of a config file
   *
   * @param string $file Path to the file
   *
   * @return mixed Content of the file
   */
  static public function getConfig($file)
  {
    return sfYaml::load(self::get($file));
  }

  /**
   * Returns the content of a cache file
   *
   * @param string $file Path to the file
   * @param boolean $assoc When FALSE, returned objects wonÄt be converted in associative arrays
   *
   * @return mixed Content of the file
   */
  static public function getCache($file, $assoc = true)
  {
    return json_decode(self::get($file), $assoc);
  }

  /**
   * Puts content in a file
   *
   * @param string $file Path to the file
   * @param string $content Content for the file
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function put($file, $content)
  {
    rex_dir::create(dirname($file));

    if(file_put_contents($file, $content) !== false)
    {
      chmod($file, rex::getFilePerm());
      return true;
    }

    return false;
  }

  /**
   * Puts content in a config file
   *
   * @param string $file Path to the file
   * @param mixed $content Content for the file
   * @param integer $inline The level where you switch to inline YAML
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function putConfig($file, $content, $inline = 3)
  {
    return self::put($file, sfYaml::dump($content, $inline));
  }

  /**
   * Puts content in a cache file
   *
   * @param string $file Path to the file
   * @param mixed $content Content for the file
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function putCache($file, $content)
  {
    return self::put($file, json_encode($content));
  }

  /**
   * Copies a file
   *
   * @param string $srcfile Path of the source file
   * @param string $dstfile Path of the destination file or directory
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function copy($srcfile, $dstfile)
  {
    if(is_file($srcfile))
    {
      if(is_dir($dstfile))
      {
        $dstfile = rtrim($dstfile, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($srcfile);
      }

      if(copy($srcfile, $dstfile))
      {
        touch($dstfile, filemtime($srcfile));
        chmod($dstfile, rex::getFilePerm());
        return true;
      }
    }
    return false;
  }

  /**
   * Deletes a file
   *
   * @param string $file Path of the file
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function delete($file)
  {
    if(file_exists($file))
    {
      return unlink($file);
    }
    return true;
  }

  /**
   * Extracts the extension of the given filename
   *
   * @param string $filename Filename
   *
   * @return string Extension of $filename
   */
  static public function extension($filename)
  {
    return substr(strrchr($filename, "."), 1);
  }

	/**
   * Formates the filesize of the given file into a userfriendly form
   *
   * @params string|int $fileOrSize Path to the file or filesize
   *
   * @return string Formatted filesize
   */
  static public function formattedSize($fileOrSize, $format = array())
  {
    return rex_formatter :: format(is_file($fileOrSize) ? filesize($fileOrSize) : $fileOrSize, 'filesize', $format);
  }
}