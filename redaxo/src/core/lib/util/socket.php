<?php

/**
* Class for sockets
*
* @author gharlan
*/
class rex_socket
{
  private $prefix;
  private $host;
  private $path;
  private $port;
  private $timeout;
  private $headers = array();
  private $fp;
  private $chunked = false;
  private $chunkPos = 0;
  private $chunkLength = 0;
  private $status;
  private $statusMessage;
  private $header;
  private $body;

  public function __construct($host, $path = '/', $port = 80, $prefix = '', $timeout = 15)
  {
    $this->prefix = $prefix;
    $this->host = $host;
    $this->path = $path;
    $this->port = $port;
    $this->timeout = $timeout;
  }

  static public function createByUrl($url, $timeout = 15)
  {
    $parts = parse_url($url);
    if(!isset($parts['host']))
    {
      throw new rex_socket_exception('It isn\'t possible to parse the URL "'. $url .'"!');
    }
    $host = $parts['host'];
    $path = (isset($parts['path'])     ? $parts['path']          : '/')
          . (isset($parts['query'])    ? '?'. $parts['query']    : '')
          . (isset($parts['fragment']) ? '#'. $parts['fragment'] : '');
    $port = 80;
    $prefix = '';
    if(isset($parts['scheme']))
    {
      $supportedProtocols = array('http', 'https');
      if(!in_array($parts['scheme'], $supportedProtocols))
      {
        throw new rex_socket_exception('Unsupported protocol "'. $parts['scheme'] .'". Supported protocols are '. implode(', ', $supportedProtocols). '.');
      }
      if($parts['scheme'] == 'https')
      {
        $prefix = 'ssl://';
        $port = 443;
      }
    }
    $port = isset($parts['port']) ? $parts['port'] : $port;
    return new self($host, $path, $port, $prefix, $timeout);
  }

  public function addHeader($key, $value)
  {
    $this->headers[$key] = $value;
  }

  public function addBasicAuthorization($user, $password)
  {
    $this->addHeader('Authorization', 'Basic '. base64_encode($user .':'. $password));
  }

  public function doGet()
  {
    $this->doRequest('GET');
  }

  public function doPost($data = '', array $files = array())
  {
    if(is_array($data) && !empty($files))
    {
      $data = function($fp) use ($data, $files)
      {
        $boundary = '----------6n2Yd9bk2liD6piRHb5xF6';
        $eol = "\r\n";
        fwrite($fp, 'Content-Type: multipart/form-data; boundary='. $boundary . $eol);
        $dataFormat = '--'. $boundary . $eol . 'Content-Disposition: form-data; name="%s"'. $eol . $eol;
        $fileFormat = '--'. $boundary . $eol . 'Content-Disposition: form-data; name="%s"; filename="%s"'. $eol .'Content-Type: %s'. $eol . $eol;
        $end = '--'. $boundary .'--'. $eol;
        $length = 0;
        $temp = explode('&', http_build_query($data, '', '&'));
        $data = array();
        $partLength = strlen(sprintf($dataFormat, '') . $eol);
        foreach($temp as $t)
        {
          list($key, $value) = array_map('urldecode', explode('=', $t, 2));
          $data[$key] = $value;
          $length += $partLength + strlen($key) + strlen($value);
        }
        $partLength = strlen(sprintf($fileFormat, '', '', '') . $eol);
        foreach($files as $key => $file)
        {
          $length += $partLength + strlen($key) + strlen(basename($file['path'])) + strlen($file['type']) + filesize($file['path']);
        }
        $length += strlen($end);
        fwrite($fp, 'Content-Length: '. $length . $eol . $eol);
        foreach($data as $key => $value)
        {
          fwrite($fp, sprintf($dataFormat, $key) . $value . $eol);
        }
        foreach($files as $key => $file)
        {
          fwrite($fp, sprintf($fileFormat, $key, basename($file['path']), $file['type']));
          $file = fopen($file['path'], 'rb');
          while(!feof($file))
          {
            fwrite($fp, fread($file, 1024));
          }
          fclose($file);
          fwrite($fp, $eol);
        }
        fwrite($fp, $end);
      };
    }
    else
    {
      if(is_array($data))
        $data = http_build_query($data);
      $this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
    }
    $this->doRequest('POST', $data);
  }

  public function doDelete()
  {
    $this->doRequest('DELETE');
  }

  public function doRequest($method, $data = '')
  {
    if(!is_string($data) && !is_callable($data))
    {
      throw new rex_exception(sprintf('Expecting $data to be a string or a callable, but %s given!', gettype($data)));
    }
    if(!($this->fp = @fsockopen($this->prefix . $this->host, $this->port, $errno, $errstr)))
    {
      throw new rex_socket_exception($errstr .' ('. $errno .')');
    }

    stream_set_timeout($this->fp, $this->timeout);

    $eol = "\r\n";
    $headers = array();
    $headers[] = strtoupper($method) .' '. $this->path .' HTTP/1.1';
    $headers[] = 'Host: '. $this->host;
    $headers[] = 'Connection: Close';
    foreach($this->headers as $key => $value)
    {
      $headers[] = $key .': '. $value;
    }
    foreach($headers as $header)
    {
      fwrite($this->fp, str_replace(array("\r", "\n"), '', $header) . $eol);
    }
    if(!is_callable($data))
    {
      fwrite($this->fp, 'Content-Length: '. strlen($data) . $eol);
      fwrite($this->fp, $eol . $data);
    }
    else
    {
      call_user_func($data, $this->fp);
    }
    $this->headers = array();

    $meta = stream_get_meta_data($this->fp);
    if($meta['timed_out'])
    {
      throw new rex_socket_exception('Timeout!');
    }

    while(!feof($this->fp) && strpos($this->header, "\r\n\r\n") === false)
    {
      $this->header .= fgets($this->fp);
    }
    if(preg_match('@^HTTP/1\.\d ([0-9]{3}) (\V*)@', $this->getHeader(), $matches))
    {
      $this->status = intval($matches[1]);
      $this->statusMessage = $matches[2];
    }
    $this->chunked = stripos($this->header, 'transfer-encoding: chunked') !== false;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function getStatusMessage()
  {
    return $this->statusMessage;
  }

  public function getHeader($key = null, $default = null)
  {
    if($key === null)
    {
      return $this->header;
    }
    $key = strtolower($key);
    if(isset($this->headers[$key]))
    {
      return $this->headers[$key];
    }
    if(preg_match('@^'. preg_quote($key, '@') .': (\V*)@im', $this->header, $matches))
    {
      return $this->headers[$key] = $matches[1];
    }
    return $this->headers[$key] = $default;
  }

  public function getBufferedBody($length = 1024)
  {
    if(feof($this->fp))
    {
      return false;
    }
    if($this->chunked)
    {
      if($this->chunkPos == 0)
      {
        $this->chunkLength = hexdec(fgets($this->fp));
        if($this->chunkLength == 0)
        {
          return false;
        }
      }
      $pos = ftell($this->fp);
      $buf = fread($this->fp, min($length, $this->chunkLength - $this->chunkPos));
      $this->chunkPos += ftell($this->fp) - $pos;
      if($this->chunkPos >= $this->chunkLength)
      {
        fgets($this->fp);
        $this->chunkPos = 0;
        $this->chunkLength = 0;
      }
      return $buf;
    }
    else
    {
      return fread($this->fp, $length);
    }
  }

  public function getBody()
  {
    if($this->body === null)
    {
      while(($buf = $this->getBufferedBody()) !== false)
      {
        $this->body .= $buf;
      }
    }
    return $this->body;
  }

  public function writeBodyTo($resource)
  {
    while(($buf = $this->getBufferedBody()) !== false)
    {
      fwrite($resource, $buf);
    }
  }

  public function __destruct()
  {
    if(is_resource($this->fp))
    {
      fclose($this->fp);
    }
  }
}

class rex_socket_exception extends rex_exception {}