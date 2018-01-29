<?php

/**
 * Class for sockets.
 *
 * Example:
 * <code>
 *     try {
 *         $socket = rex_socket::factory('www.example.com');
 *         $socket->setPath('/url/to/my/resource?param=1');
 *         $response = $socket->doGet();
 *         if($response->isOk()) {
 *             $body = $response->getBody();
 *         }
 *     } catch(rex_socket_exception $e) {
 *         // error message: $e->getMessage()
 *     }
 * </code>
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_socket
{
    protected $host;
    protected $port;
    protected $ssl;
    protected $path = '/';
    protected $timeout = 15;
    protected $followRedirects = false;
    protected $headers = [];
    protected $stream;

    /**
     * Constructor.
     *
     * @param string $host Host name
     * @param int    $port Port number
     * @param bool   $ssl  SSL flag
     */
    protected function __construct($host, $port = 80, $ssl = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->ssl = $ssl;

        $this->addHeader('Host', $this->host);
        $this->addHeader('User-Agent', 'REDAXO/' . rex::getVersion());
        $this->addHeader('Connection', 'Close');
    }

    /**
     * Factory method.
     *
     * @param string $host Host name
     * @param int    $port Port number
     * @param bool   $ssl  SSL flag
     *
     * @return static Socket instance
     *
     * @see rex_socket::factoryUrl()
     */
    public static function factory($host, $port = 80, $ssl = false)
    {
        if (get_called_class() === __CLASS__ && ($proxy = rex::getProperty('socket_proxy'))) {
            return rex_socket_proxy::factoryUrl($proxy)->setDestination($host, $port, $ssl);
        }

        return new static($host, $port, $ssl);
    }

    /**
     * Creates a socket by a full URL.
     *
     * @param string $url URL
     *
     * @throws rex_socket_exception
     *
     * @return static Socket instance
     *
     * @see rex_socket::factory()
     */
    public static function factoryUrl($url)
    {
        $parts = self::parseUrl($url);

        return static::factory($parts['host'], $parts['port'], $parts['ssl'])->setPath($parts['path']);
    }

    /**
     * Sets the path.
     *
     * @param string $path
     *
     * @return $this Current socket
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Adds a header to the current request.
     *
     * @param string $key
     * @param string $value
     *
     * @return $this Current socket
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Adds the basic authorization header to the current request.
     *
     * @param string $user
     * @param string $password
     *
     * @return $this Current socket
     */
    public function addBasicAuthorization($user, $password)
    {
        $this->addHeader('Authorization', 'Basic ' . base64_encode($user . ':' . $password));

        return $this;
    }

    /**
     * Sets the timeout for the connection.
     *
     * @param int $timeout Timeout
     *
     * @return $this Current socket
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Sets number of redirects that should be followed automatically.
     *
     * The method only affects GET requests.
     *
     * @param false|int $redirects Number of max redirects
     *
     * @return $this Current socket
     */
    public function followRedirects($redirects)
    {
        if ($redirects < 0) {
            throw new InvalidArgumentException(sprintf('$redirects must be `null` or an int >= 0, given "%s".', $redirects));
        }

        $this->followRedirects = $redirects;

        return $this;
    }

    /**
     * Makes a GET request.
     *
     * @return rex_socket_response Response
     *
     * @throws rex_socket_exception
     */
    public function doGet()
    {
        return $this->doRequest('GET');
    }

    /**
     * Makes a POST request.
     *
     * @param string|array|callable $data  Body data as string or array (POST parameters) or a callback for writing the body
     * @param array                 $files Files array, e.g. `array('myfile' => array('path' => $path, 'type' => 'image/png'))`
     *
     * @return rex_socket_response Response
     *
     * @throws rex_socket_exception
     */
    public function doPost($data = '', array $files = [])
    {
        if (is_array($data) && !empty($files)) {
            $data = function ($stream) use ($data, $files) {
                $boundary = '----------6n2Yd9bk2liD6piRHb5xF6';
                $eol = "\r\n";
                fwrite($stream, 'Content-Type: multipart/form-data; boundary=' . $boundary . $eol);
                $dataFormat = '--' . $boundary . $eol . 'Content-Disposition: form-data; name="%s"' . $eol . $eol;
                $fileFormat = '--' . $boundary . $eol . 'Content-Disposition: form-data; name="%s"; filename="%s"' . $eol . 'Content-Type: %s' . $eol . $eol;
                $end = '--' . $boundary . '--' . $eol;
                $length = 0;
                $temp = explode('&', rex_string::buildQuery($data));
                $data = [];
                $partLength = rex_string::size(sprintf($dataFormat, '') . $eol);
                foreach ($temp as $t) {
                    list($key, $value) = array_map('urldecode', explode('=', $t, 2));
                    $data[$key] = $value;
                    $length += $partLength + rex_string::size($key) + rex_string::size($value);
                }
                $partLength = rex_string::size(sprintf($fileFormat, '', '', '') . $eol);
                foreach ($files as $key => $file) {
                    $length += $partLength + rex_string::size($key) + rex_string::size(basename($file['path'])) + rex_string::size($file['type']) + filesize($file['path']);
                }
                $length += rex_string::size($end);
                fwrite($stream, 'Content-Length: ' . $length . $eol . $eol);
                foreach ($data as $key => $value) {
                    fwrite($stream, sprintf($dataFormat, $key) . $value . $eol);
                }
                foreach ($files as $key => $file) {
                    fwrite($stream, sprintf($fileFormat, $key, basename($file['path']), $file['type']));
                    $file = fopen($file['path'], 'rb');
                    while (!feof($file)) {
                        fwrite($stream, fread($file, 1024));
                    }
                    fclose($file);
                    fwrite($stream, $eol);
                }
                fwrite($stream, $end);
            };
        } elseif (!is_callable($data)) {
            if (is_array($data)) {
                $data = rex_string::buildQuery($data);
                $this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            }
        }
        return $this->doRequest('POST', $data);
    }

    /**
     * Makes a DELETE request.
     *
     * @return rex_socket_response Response
     *
     * @throws rex_socket_exception
     */
    public function doDelete()
    {
        return $this->doRequest('DELETE');
    }

    /**
     * Makes a request.
     *
     * @param string          $method HTTP method, e.g. "GET"
     * @param string|callable $data   Body data as string or a callback for writing the body
     *
     * @return rex_socket_response Response
     *
     * @throws InvalidArgumentException
     */
    public function doRequest($method, $data = '')
    {
        if (!is_string($data) && !is_callable($data)) {
            throw new InvalidArgumentException(sprintf('Expecting $data to be a string or a callable, but %s given!', gettype($data)));
        }

        if (!$this->ssl) {
            rex_logger::logError(E_WARNING, 'You should not use non-secure socket connections while connecting to "'. $this->host .'"!', __FILE__, __LINE__);
        }

        $this->openConnection();
        $response = $this->writeRequest($method, $this->path, $this->headers, $data);

        if ('GET' !== $method || !$this->followRedirects || !$response->isRedirection()) {
            return $response;
        }

        $location = $response->getHeader('location');

        if (!$location) {
            return $response;
        }

        if (false === strpos($location, '//')) {
            $socket = self::factory($this->host, $this->port, $this->ssl)->setPath($location);
        } else {
            $socket = self::factoryUrl($location);

            if ($this->ssl && !$socket->ssl) {
                return $response;
            }
        }

        $socket->setTimeout($this->timeout);
        $socket->followRedirects($this->followRedirects - 1);

        foreach ($this->headers as $key => $value) {
            if ('Host' !== $key) {
                $socket->addHeader($key, $value);
            }
        }

        return $socket->doGet();
    }

    /**
     * Opens the socket connection.
     *
     * @throws rex_socket_exception
     */
    protected function openConnection()
    {
        $host = ($this->ssl ? 'ssl://' : '') . $this->host;

        $prevError = null;
        set_error_handler(function ($errno, $errstr) use (&$prevError) {
            if (null === $prevError) {
                $prevError = $errstr;
            }
        });

        try {
            $this->stream = @fsockopen($host, $this->port, $errno, $errstr);
        } finally {
            restore_error_handler();
        }

        if ($this->stream) {
            stream_set_timeout($this->stream, $this->timeout);

            return;
        }

        if ($errstr) {
            throw new rex_socket_exception($errstr . ' (' . $errno . ')');
        }

        if ($prevError) {
            throw new rex_socket_exception($prevError);
        }

        throw new rex_socket_exception('Unknown error.');
    }

    /**
     * Writes a request to the opened connection.
     *
     * @param string          $method  HTTP method, e.g. "GET"
     * @param string          $path    Path
     * @param array           $headers Headers
     * @param string|callable $data    Body data as string or a callback for writing the body
     *
     * @throws rex_socket_exception
     *
     * @return rex_socket_response Response
     */
    protected function writeRequest($method, $path, array $headers = [], $data = '')
    {
        $eol = "\r\n";
        $headerStrings = [];
        $headerStrings[] = strtoupper($method) . ' ' . $path . ' HTTP/1.1';
        foreach ($headers as $key => $value) {
            $headerStrings[] = $key . ': ' . $value;
        }
        foreach ($headerStrings as $header) {
            fwrite($this->stream, str_replace(["\r", "\n"], '', $header) . $eol);
        }
        if (!is_callable($data)) {
            fwrite($this->stream, 'Content-Length: ' . rex_string::size($data) . $eol);
            fwrite($this->stream, $eol . $data);
        } else {
            call_user_func($data, $this->stream);
        }

        $meta = stream_get_meta_data($this->stream);
        if (isset($meta['timed_out']) && $meta['timed_out']) {
            throw new rex_socket_exception('Timeout!');
        }

        return new rex_socket_response($this->stream);
    }

    /**
     * Parses a full URL and returns an array with the keys "host", "port", "ssl" and "path".
     *
     * @param string $url Full URL
     *
     * @return array URL parts
     *
     * @throws rex_socket_exception
     */
    protected static function parseUrl($url)
    {
        $parts = parse_url($url);
        if ($parts !== false && !isset($parts['host']) && strpos($url, 'http') !== 0) {
            $parts = parse_url('http://' . $url);
        }
        if ($parts === false || !isset($parts['host'])) {
            throw new rex_socket_exception('It isn\'t possible to parse the URL "' . $url . '"!');
        }

        $port = 80;
        $ssl = false;
        if (isset($parts['scheme'])) {
            $supportedProtocols = ['http', 'https'];
            if (!in_array($parts['scheme'], $supportedProtocols)) {
                throw new rex_socket_exception('Unsupported protocol "' . $parts['scheme'] . '". Supported protocols are ' . implode(', ', $supportedProtocols) . '.');
            }
            if ($parts['scheme'] == 'https') {
                $ssl = true;
                $port = 443;
            }
        }
        $port = isset($parts['port']) ? (int) $parts['port'] : $port;

        $path = (isset($parts['path']) ? $parts['path'] : '/')
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');

        return [
            'host' => $parts['host'],
            'port' => $port,
            'ssl' => $ssl,
            'path' => $path,
        ];
    }
}

/**
 * Socket exception.
 *
 * @see rex_socket
 *
 * @package redaxo\core
 */
class rex_socket_exception extends rex_exception
{
}
