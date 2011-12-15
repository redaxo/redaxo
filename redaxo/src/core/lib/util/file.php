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
   * @param mixed $default Default value
   * @return mixed Content of the file or default value if the file isn't readable
   */
  static public function get($file, $default = null)
  {
    return is_readable($file) ? file_get_contents($file) : $default;
  }

  /**
   * Returns the content of a config file
   *
   * @param string $file Path to the file
   * @param mixed $default Default value
   * @return mixed Content of the file or default value if the file isn't readable
   */
  static public function getConfig($file, $default = array())
  {
    $content = self::get($file);
    return $content === null ? $default : sfYaml::load($content);
  }

  /**
   * Returns the content of a cache file
   *
   * @param string $file Path to the file
   * @param mixed $default Default value
   * @return mixed Content of the file or default value if the file isn't readable
   */
  static public function getCache($file, $default = array())
  {
    $content = self::get($file);
    return $content === null ? $default : json_decode($content, true);
  }

  /**
   * Puts content in a file
   *
   * @param string $file Path to the file
   * @param string $content Content for the file
   * @return boolean TRUE on success, FALSE on failure
   */
  static public function put($file, $content)
  {
    if(!rex_dir::create(dirname($file)) || file_exists($file) && !is_writable($file))
    {
      return false;
    }

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

      // file_exists(dirname($dstfile) .'/.') checks if the parent directory has the executable permission
      // is_executable($directory) does not work on all systems
      if(file_exists(dirname($dstfile) .'/.') && (!file_exists($dstfile) || is_writable($dstfile)) && copy($srcfile, $dstfile))
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
   * @return string Formatted filesize
   */
  static public function formattedSize($fileOrSize, $format = array())
  {
    return rex_formatter :: format(is_file($fileOrSize) ? filesize($fileOrSize) : $fileOrSize, 'filesize', $format);
  }
}