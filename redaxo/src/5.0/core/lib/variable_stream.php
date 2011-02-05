<?php

class rex_variableStream
{
  static private
    $registeredProtocols = array(),
    $nextContent;

  private
    $position,
    $content;

  static public function factory($content, $protocol, $path)
  {
    if(!is_string($content))
    {
      throw new rexException('Expecting $content to be a string!');
    }
    if(!is_string($protocol) || empty($protocol))
    {
      throw new rexException('Expecting $protocol to be a strin and not empty!');
    }
    if(!is_string($path) && !is_int($path) || empty($path))
    {
      throw new rexException('Expecting $path to be a string or integer and not empty!');
    }

    if(!in_array($protocol, self::$registeredProtocols))
    {
      if(in_array($protocol, stream_get_wrappers()))
      {
        throw new rexException('Protocol "'.$protocol.'" already exists!');
      }
      stream_wrapper_register($protocol, __CLASS__);
      self::$registeredProtocols[] = $protocol;
    }

    self::$nextContent = $content;

    return $protocol .'://'. $path;
  }

  public function stream_open($path, $mode, $options, &$opened_path)
  {
    if(!is_string(self::$nextContent))
    {
      return false;
    }

    $this->position = 0;
    $this->content = self::$nextContent;
    self::$nextContent = null;

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