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
      throw new rex_exception('It isn\'t possible to parse the URL "'. $url .'"!');
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
        throw new rex_exception('Unsupported protocol "'. $parts['scheme'] .'". Supported protocols are '. implode(', ', $supportedProtocols). '.');
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

  public function doGet()
  {
    $this->doRequest('GET');
  }

  public function doPost($data = '')
  {
    if(is_array($data))
    {
      $data = http_build_query($data);
    }
    $this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
    $this->doRequest('POST', $data);
  }

  public function doRequest($method, $data = '')
  {
    if(!($this->fp = @fsockopen($this->prefix . $this->host, $this->port, $errno, $errstr)))
    {
      throw new rex_exception($errstr .' ('. $errno .')');
    }

    stream_set_timeout($this->fp, $this->timeout);

    $eol = "\r\n";
    $headers = array();
    $headers[] = strtoupper($method) .' '. $this->path .' HTTP/1.1';
    $headers[] = 'Host: '. $this->host;
    $headers[] = 'Content-Length: '. strlen($data);
    foreach($this->headers as $key => $value)
    {
      $headers[] = $key .': '. $value;
    }
    $headers[] = 'Connection: Close';
    $out = '';
    foreach($headers as $header)
    {
      $out .= str_replace(array("\r", "\n"), '', $header) . $eol;
    }
    $out .= $eol . $data;

    fwrite($this->fp, $out);

    $meta = stream_get_meta_data($this->fp);
    if($meta['timed_out'])
    {
      throw new rex_exception('Timeout!');
    }

    while(!feof($this->fp) && strpos($this->header, "\r\n\r\n") === false)
    {
      $this->header .= fgets($this->fp);
    }
    if(preg_match('@^HTTP/1\.1 ([0-9]{3})@', $this->getHeader(), $matches))
    {
      $this->status = intval($matches[1]);
    }
    $this->chunked = stripos($this->header, 'transfer-encoding: chunked') !== false;
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function getHeader()
  {
    return $this->header;
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