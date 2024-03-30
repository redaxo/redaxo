<?php

namespace Redaxo\Core\HttpClient;

use Override;
use rex_socket_exception;

use const STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

/**
 * Class for HttpClient requests over a proxy.
 */
class ProxyRequest extends Request
{
    protected string $destinationHost;
    protected int $destinationPort;
    protected bool $destinationSsl;

    /**
     * Sets the destination.
     *
     * @param string $host Host name
     * @param int $port Port number
     * @param bool $ssl SSL flag
     *
     * @return $this
     */
    public function setDestination(string $host, int $port = 443, bool $ssl = true): static
    {
        $this->destinationHost = $host;
        $this->destinationPort = $port;
        $this->destinationSsl = $ssl;

        $this->addHeader('Host', $host);

        return $this;
    }

    /**
     * Sets the destination by a full URL.
     *
     * @param string $url Full URL
     *
     * @return $this
     */
    public function setDestinationUrl(string $url): static
    {
        $parts = self::parseUrl($url);

        return $this->setDestination($parts['host'], $parts['port'], $parts['ssl'])->setPath($parts['path']);
    }

    #[Override]
    protected function openConnection(): void
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
            stream_context_set_option($this->stream, 'ssl', 'SNI_enabled', true);
            stream_context_set_option($this->stream, 'ssl', 'peer_name', $this->destinationHost);
            stream_socket_enable_crypto($this->stream, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
        } else {
            unset($this->headers['Connection']);
            $this->addHeader('Proxy-Connection', 'Close');
            $this->path = 'http://' . $this->destinationHost . ':' . $this->destinationPort . $this->path;
        }
    }
}
