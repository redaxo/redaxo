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
    if(!($fp = @fsockopen($this->prefix . $this->host, $this->port, $errno, $errstr)))
    {
      throw new rex_exception($errstr .' ('. $errno .')');
    }

    stream_set_timeout($fp, $this->timeout);

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

    fwrite($fp, $out);

    $meta = stream_get_meta_data($fp);
    if($meta['timed_out'])
    {
      fclose($fp);
      throw new rex_exception('Timeout!');
    }

    $this->header = '';
    $this->body = '';
    while(!feof($fp))
    {
      $this->body .= fgets($fp);

      if($this->header == '' && ($headEnd = strpos($this->body, $eol.$eol)) !== false)
      {
        $this->header = substr($this->body, 0, $headEnd); // extract http header
        $this->body = substr($this->body, $headEnd+4); // trim buf to contain only http data
      }
    }
    fclose($fp);

    if(preg_match('@^HTTP\/1\.1 ([0-9]{3})@', $this->header, $matches))
    {
      $this->status = intval($matches[1]);
    }
  }

  public function getStatus()
  {
    return $this->status;
  }

  public function getHeader()
  {
    return $this->header;
  }

  public function getBody()
  {
    return $this->body;
  }
}