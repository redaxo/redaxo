<?php

/**
 * Class for sockets over a proxy
 *
 * @author gharlan
 */
class rex_socket_proxy extends rex_socket
{
  protected
    $destinationHost,
    $destinationPort,
    $destinationSsl;

  /**
   * Constructor
   *
   * @param string $host Proxy host name
   * @param integer $port Proxy port number
   * @param boolean $ssl Proxy SSL flag
   */
  protected function __construct($host, $port = 80, $ssl = false)
  {
    parent::__construct($host, $port, $ssl);

    unset($this->headers['Host']);
    unset($this->headers['Connection']);
    $this->addHeader('Proxy-Connection', 'Close');
  }

  /**
   * Sets the destination
   *
   * @param string $host Host name
   * @param integer $port Port number
   * @param boolean $ssl SSL flag
   * @return self Current socket
   */
  public function setDestination($host, $port = 80, $ssl = false)
  {
    $this->destinationHost = $host;
    $this->destinationPort = $port;
    $this->destinationSsl = $ssl;

    $this->addHeader('Host', $host . ':' . $port);

    return $this;
  }

  /**
   * Sets the destination by a full URL
   *
   * @param string $url Full URL
   * @return self Current socket
   */
  public function setDestinationUrl($url)
  {
    $parts = self::parseUrl($url);

    return $this->setDestination($parts['host'], $parts['port'], $parts['ssl'])->setPath($parts['path']);
  }

  /* (non-PHPdoc)
   * @see rex_socket::getPath()
   */
  protected function getPath()
  {
    return ($this->ssl ? 'https' : 'http') . '://' . $this->destinationHost . ':' . $this->destinationPort . $this->path;
  }
}
