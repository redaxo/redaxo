<?php

/**
 * Class for rex_socket responses
 *
 * @author gharlan
 */
class rex_socket_response
{
  private $fp;
  private $chunked = false;
  private $chunkPos = 0;
  private $chunkLength = 0;
  private $statusCode;
  private $statusMessage;
  private $header;
  private $headers = array();
  private $body;

  public function __construct($resource)
  {
    if (!is_resource($resource))
    {
      throw new rex_exception(sprintf('Expecting $resource to be a resource, but %s given!', gettype($resource)));
    }

    $this->fp = $resource;

    while (!feof($this->fp) && strpos($this->header, "\r\n\r\n") === false)
    {
      $this->header .= fgets($this->fp);
    }
    if (preg_match('@^HTTP/1\.\d ([0-9]{3}) (\V*)@', $this->getHeader(), $matches))
    {
      $this->statusCode = intval($matches[1]);
      $this->statusMessage = $matches[2];
    }
    $this->chunked = stripos($this->header, 'transfer-encoding: chunked') !== false;
  }

  /**
  * Returns the HTTP status code, e.g. 200
  *
  * @return integer
  */
  public function getStatusCode()
  {
    return $this->statusCode;
  }

  /**
   * Returns the HTTP status message, e.g. "OK"
   */
  public function getStatusMessage()
  {
    return $this->statusMessage;
  }

  /**
   * Returns wether the status is "200 OK"
   *
   * @return boolean
   */
  public function isOk()
  {
    return $this->statusCode == 200;
  }

  /**
  * Returns wether the status class is "Informational"
  *
  * @return boolean
  */
  public function isInformational()
  {
    return $this->statusCode >= 100 && $this->statusCode < 200;
  }

  /**
  * Returns wether the status class is "Success"
  *
  * @return boolean
  */
  public function isSuccessful()
  {
    return $this->statusCode >= 200 && $this->statusCode < 300;
  }

  /**
  * Returns wether the status class is "Redirection"
  *
  * @return boolean
  */
  public function isRedirection()
  {
    return $this->statusCode >= 300 && $this->statusCode < 400;
  }

  /**
  * Returns wether the status class is "Client Error"
  *
  * @return boolean
  */
  public function isClientError()
  {
    return $this->statusCode >= 400 && $this->statusCode < 500;
  }

  /**
  * Returns wether the status class is "Server Error"
  *
  * @return boolean
  */
  public function isServerError()
  {
    return $this->statusCode >= 500 && $this->statusCode < 600;
  }

  /**
  * Returns wether the status is invalid
  *
  * @return boolean
  */
  public function isInvalid()
  {
    return $this->statusCode < 100 || $this->statusCode >= 600;
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
    if ($key === null)
    {
      return $this->header;
    }
    $key = strtolower($key);
    if (isset($this->headers[$key]))
    {
      return $this->headers[$key];
    }
    if (preg_match('@^'. preg_quote($key, '@') .': (\V*)@im', $this->header, $matches))
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
    if (feof($this->fp))
    {
      return false;
    }
    if ($this->chunked)
    {
      if ($this->chunkPos == 0)
      {
        $this->chunkLength = hexdec(fgets($this->fp));
        if ($this->chunkLength == 0)
        {
          return false;
        }
      }
      $pos = ftell($this->fp);
      $buf = fread($this->fp, min($length, $this->chunkLength - $this->chunkPos));
      $this->chunkPos += ftell($this->fp) - $pos;
      if ($this->chunkPos >= $this->chunkLength)
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
    if ($this->body === null)
    {
      while (($buf = $this->getBufferedBody()) !== false)
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
    if (is_string($resource) && rex_dir::create(dirname($resource)))
    {
      $resource = fopen($resource, 'wb');
      $close = true;
    }
    if (!is_resource($resource))
    {
      return false;
    }
    $success = true;
    while ($success && ($buf = $this->getBufferedBody()) !== false)
    {
      $success = (boolean) fwrite($resource, $buf);
    }
    if ($close)
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
    if (is_resource($this->fp))
    {
      fclose($this->fp);
    }
  }
}
