<?php

class rex_variableStream
{
  static private
    $nextContent = array();

  private
    $position,
    $content;

  static public function register()
  {
    stream_wrapper_register('redaxo', __CLASS__);
  }

  static public function factory($path, $content)
  {
    if(!is_string($content))
    {
      throw new rexException('Expecting $content to be a string!');
    }
    if(!is_string($path) || empty($path))
    {
      throw new rexException('Expecting $path to be a string and not empty!');
    }

    $path = 'redaxo://'. $path;
    self::$nextContent[$path] = $content;

    return $path;
  }

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

  public function stream_read($count)
  {
    $ret = substr($this->content, $this->position, $count);
    $this->position += strlen($ret);
    return $ret;
  }

  public function stream_eof()
  {
    return $this->position >= strlen($this->content);
  }

  public function stream_stat()
  {
    return null;
  }
}