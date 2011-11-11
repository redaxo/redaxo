<?php

/**
 * Stream wrapper to include variables like files (php code will be evaluated)
 *
 * Example:
 * <code>
 * <?php
 *   include rex_stream::factory('myContent', '<?php echo "Hello World"; ?>');
 * ?>
 * </code>
 *
 * @author gharlan
 *
 * @link http://www.php.net/manual/en/class.streamwrapper.php
 */
class rex_stream
{
  static private
    $registered = false,
    $nextContent = array();

  private
    $position,
    $content;

  /**
   * Prepares a new stream
   *
   * @param string $path Virtual path which should describe the content (e.g. "template/1"), only relevant for error messages
   * @param string $content Content which will be included
   *
   * @return string Full path with protocol (e.g. "rex://template/1")
   */
  static public function factory($path, $content)
  {
    if(!is_string($path) || empty($path))
    {
      throw new rex_exception('Expecting $path to be a string and not empty!');
    }
    if(!is_string($content))
    {
      throw new rex_exception('Expecting $content to be a string!');
    }

    if(!self::$registered)
    {
      stream_wrapper_register('rex', __CLASS__);
      self::$registered = true;
    }

    $path = 'rex://'. $path;
    self::$nextContent[$path] = $content;

    return $path;
  }

  /**
   * @link http://www.php.net/manual/en/streamwrapper.stream-open.php
   */
  public function stream_open($path, $mode, $options, &$opened_path)
  {
    if(!isset(self::$nextContent[$path]) || !is_string(self::$nextContent[$path]))
    {
      return false;
    }

    $this->position = 0;
    $this->content = self::$nextContent[$path];
    unset(self::$nextContent[$path]);

    return true;
  }

  /**
   * @link http://www.php.net/manual/en/streamwrapper.stream-read.php
   */
  public function stream_read($count)
  {
    $ret = substr($this->content, $this->position, $count);
    $this->position += strlen($ret);
    return $ret;
  }

  /**
   * @link http://www.php.net/manual/en/streamwrapper.stream-eof.php
   */
  public function stream_eof()
  {
    return $this->position >= strlen($this->content);
  }

  /**
   * @link http://www.php.net/manual/en/streamwrapper.stream-stat.php
   */
  public function stream_stat()
  {
    return null;
  }

  /**
  * @link http://www.php.net/manual/en/streamwrapper.url-stat.php
  */
  public function url_stat()
  {
    return null;
  }
}