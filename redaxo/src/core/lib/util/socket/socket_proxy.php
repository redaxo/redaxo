<?php

/**
 * Class for sockets over a proxy.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_socket_proxy extends rex_socket
{
    /**
     * @var string
     */
    protected $destinationHost;
    /**
     * @var int
     */
    protected $destinationPort;
    /**
     * @var bool
     */
    protected $destinationSsl;

    /**
     * Sets the destination.
     *
     * @param string $host Host name
     * @param int    $port Port number
     * @param bool   $ssl  SSL flag
     *
     * @return $this Current socket
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
     * Sets the destination by a full URL.
     *
     * @param string $url Full URL
     *
     * @return $this Current socket
     */
    public function setDestinationUrl($url)
    {
        $parts = self::parseUrl($url);

        return $this->setDestination($parts['host'], $parts['port'], $parts['ssl'])->setPath($parts['path']);
    }

    /**
     * {@inheritdoc}
     */
    protected function openConnection()
    {
        parent::openConnection();

        if ($this->destinationSsl) {
            $headers = [
                'Host' => $this->destinationHost . ':' . $this->destinationPort,
                'Proxy-Connection' => 'Keep-Alive',
            ];
            $response = $this->writeRequest('CONNECT', $this->destinationHost . ':' . $this->destinationPort, $headers);
            if (!$response->isOk()) {
                throw new rex_socket_exception(sprintf('Couldn\'t connect to proxy server, server responds with "%s %s"', $response->getStatusCode(), $response->getStatusMessage()));
            }
            stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_SSLv3_CLIENT);
        } else {
            unset($this->headers['Connection']);
            $this->addHeader('Proxy-Connection', 'Close');
            $this->path = 'http://' . $this->destinationHost . ':' . $this->destinationPort . $this->path;
        }
    }
}
