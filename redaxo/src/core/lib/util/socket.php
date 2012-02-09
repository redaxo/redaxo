<?php

/**
* Class for sockets
*
* Example:
* <code>
* <?php
* try
* {
*   $socket = new rex_socket('www.example.com', '/path/index.php?param=1');
*   $socket->doGet();
*   if($socket->getStatus() == 200)
*     $body = $socket->getBody();
* }
* catch(rex_socket_exception $e)
* {
* 	// error message: $e->getMessage()
* }
* ?>
* </code>
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

  /**
   * Constructor
   *
   * @param string $host Host name
   * @param string $path Path
   * @param integer $port Port number
   * @param string $prefix Prefix, e.g. "ssl://"
   * @param integer $timeout Connection timeout in seconds
   *
   * @see rex_socket::createByUrl()
   */
  public function __construct($host, $path = '/', $port = 80, $prefix = '', $timeout = 15)
  {
    $this->prefix = $prefix;
    $this->host = $host;
    $this->path = $path;
    $this->port = $port;
    $this->timeout = $timeout;
  }

  /**
   * Creates a socket by a full URL
   *
   * @param string $url URL
   * @param integer $timeout Connection timeout in seconds
   * @throws rex_socket_exception
   * @return rex_socket Socket instance
   *
   * @see rex_socket::__construct()
   */
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

  /**
   * Adds a header to the current request
   *
   * @param string $key
   * @param string $value
   */
  public function addHeader($key, $value)
  {
    $this->headers[$key] = $value;
  }

  /**
   * Adds the basic authorization header to the current request
   *
   * @param string $user
   * @param string $password
   */
  public function addBasicAuthorization($user, $password)
  {
    $this->addHeader('Authorization', 'Basic '. base64_encode($user .':'. $password));
  }

  /**
   * Makes a GET request
   */
  public function doGet()
  {
    $this->doRequest('GET');
  }

  /**
   * Makes a POST request
   *
   * @param string|array|callable $data Body data as string or array (POST parameters) or a callback for writing the body
   * @param array $files Files array, e.g. <code>array('myfile' => array('path' => $path, 'type' => 'image/png'))</code>
   */
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
        $partLength = rex_string::size(sprintf($dataFormat, '') . $eol);
        foreach($temp as $t)
        {
          list($key, $value) = array_map('urldecode', explode('=', $t, 2));
          $data[$key] = $value;
          $length += $partLength + rex_string::size($key) + rex_string::size($value);
        }
        $partLength = rex_string::size(sprintf($fileFormat, '', '', '') . $eol);
        foreach($files as $key => $file)
        {
          $length += $partLength + rex_string::size($key) + rex_string::size(basename($file['path'])) + rex_string::size($file['type']) + filesize($file['path']);
        }
        $length += rex_string::size($end);
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
    elseif(!is_callable($data))
    {
      if(is_array($data))
        $data = http_build_query($data);
      $this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
    }
    $this->doRequest('POST', $data);
  }

  /**
   * Makes a DELETE request
   */
  public function doDelete()
  {
    $this->doRequest('DELETE');
  }

  /**
   * Makes a request
   *
   * @param string $method HTTP method, e.g. "GET"
   * @param string|callable $data Body data as string or a callback for writing the body
   * @throws rex_exception
   * @throws rex_socket_exception
   */
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
      fwrite($this->fp, 'Content-Length: '. rex_string::size($data) . $eol);
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

  /**
   * Returns the HTTP status code, e.g. 200
   *
   * @return integer
   */
  public function getStatus()
  {
    return $this->status;
  }

  /**
   * Returns the HTTP status message, e.g. "OK"
   */
  public function getStatusMessage()
  {
    return $this->statusMessage;
  }

  /**
   * Returns the header for the given key, or the entire header if no key is given
   *
   * @param string $key Header key
   * @param string $default Default value (is returned if the header is not set)
   * @return string
   */
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

  /**
   * Returns up to <code>$length</code> bytes from the body, or <code>false</code> if the end is reached
   *
   * @param integer $length Max number of bytes
   * @return boolean|string
   */
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

  /**
   * Returns the entire body
   *
   * @return string
   */
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

  /**
   * Writes the body to the given resource
   *
   * @param string|resource $resource File path or file pointer
   * @return boolean <code>true</code> on success, <code>false</code> on failure
   */
  public function writeBodyTo($resource)
  {
    $close = false;
    if(is_string($resource) && rex_dir::create(dirname($resource)))
    {
      $resource = fopen($resource, 'wb');
      $close = true;
    }
    if(!is_resource($resource))
    {
      return false;
    }
    $success = true;
    while($success && ($buf = $this->getBufferedBody()) !== false)
    {
      $success = (boolean) fwrite($resource, $buf);
    }
    if($close)
    {
      fclose($resource);
    }
    return $success;
  }

  /**
   * Destructor, closes the socket resource
   */
  public function __destruct()
  {
    if(is_resource($this->fp))
    {
      fclose($this->fp);
    }
  }
}

/**
 * Socket exception
 *
 * @see rex_socket
 */
class rex_socket_exception extends rex_exception {}