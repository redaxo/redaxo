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
*   // error message: $e->getMessage()
* }
* ?>
* </code>
*
* @author gharlan
*/
class rex_socket
{
  private $transport;
  private $host;
  private $path;
  private $port;
  private $timeout = 15;
  private $headers = array();

  /**
   * Constructor
   *
   * @param string $host Host name
   * @param string $path Path
   * @param integer $port Port number
   * @param string $transport Transport, e.g. "ssl"
   *
   * @see rex_socket::createByUrl()
   */
  public function __construct($host, $path = '/', $port = 80, $transport = '')
  {
    $this->transport = $transport;
    $this->host = $host;
    $this->path = $path;
    $this->port = $port;
  }

  /**
   * Creates a socket by a full URL
   *
   * @param string $url URL
   * @throws rex_socket_exception
   * @return rex_socket Socket instance
   *
   * @see rex_socket::__construct()
   */
  static public function createByUrl($url)
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
    $transport = '';
    if(isset($parts['scheme']))
    {
      $supportedProtocols = array('http', 'https');
      if(!in_array($parts['scheme'], $supportedProtocols))
      {
        throw new rex_socket_exception('Unsupported protocol "'. $parts['scheme'] .'". Supported protocols are '. implode(', ', $supportedProtocols). '.');
      }
      if($parts['scheme'] == 'https')
      {
        $transport = 'ssl';
        $port = 443;
      }
    }
    $port = isset($parts['port']) ? $parts['port'] : $port;
    return new self($host, $path, $port, $transport);
  }

  /**
   * Adds a header to the current request
   *
   * @param string $key
   * @param string $value
   * @return self Current socket
   */
  public function addHeader($key, $value)
  {
    $this->headers[$key] = $value;

    return $this;
  }

  /**
   * Adds the basic authorization header to the current request
   *
   * @param string $user
   * @param string $password
   * @return self Current socket
   */
  public function addBasicAuthorization($user, $password)
  {
    $this->addHeader('Authorization', 'Basic '. base64_encode($user .':'. $password));

    return $this;
  }

  /**
   * Sets the timeout for the connection
   *
   * @param int $timeout Timeout
   * @return self Current socket
   */
  public function setTimeout($timeout)
  {
    $this->timeout = $timeout;

    return $this;
  }

  /**
   * Makes a GET request
   *
   * @return rex_socket_response Response
   */
  public function doGet()
  {
    return $this->doRequest('GET');
  }

  /**
   * Makes a POST request
   *
   * @param string|array|callable $data Body data as string or array (POST parameters) or a callback for writing the body
   * @param array $files Files array, e.g. <code>array('myfile' => array('path' => $path, 'type' => 'image/png'))</code>
   * @return rex_socket_response Response
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
    return $this->doRequest('POST', $data);
  }

  /**
   * Makes a DELETE request
   *
   * @return rex_socket_response Response
   */
  public function doDelete()
  {
    return $this->doRequest('DELETE');
  }

  /**
   * Makes a request
   *
   * @param string $method HTTP method, e.g. "GET"
   * @param string|callable $data Body data as string or a callback for writing the body
   * @return rex_socket_response Response
   * @throws rex_exception
   * @throws rex_socket_exception
   */
  public function doRequest($method, $data = '')
  {
    if(!is_string($data) && !is_callable($data))
    {
      throw new rex_exception(sprintf('Expecting $data to be a string or a callable, but %s given!', gettype($data)));
    }

    $host = ($this->transport ? $this->transport . '://' : '') . $this->host;
    if(!($fp = @fsockopen($host, $this->port, $errno, $errstr)))
    {
      throw new rex_socket_exception($errstr .' ('. $errno .')');
    }

    stream_set_timeout($fp, $this->timeout);

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
      fwrite($fp, str_replace(array("\r", "\n"), '', $header) . $eol);
    }
    if(!is_callable($data))
    {
      fwrite($fp, 'Content-Length: '. rex_string::size($data) . $eol);
      fwrite($fp, $eol . $data);
    }
    else
    {
      call_user_func($data, $fp);
    }
    $this->headers = array();

    $meta = stream_get_meta_data($fp);
    if($meta['timed_out'])
    {
      throw new rex_socket_exception('Timeout!');
    }

    return new rex_socket_response($fp);
  }
}

/**
 * Socket exception
 *
 * @see rex_socket
 */
class rex_socket_exception extends rex_exception {}
