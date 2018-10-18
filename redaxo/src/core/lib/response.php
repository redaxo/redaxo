<?php

/**
 * HTTP1.1 Client Cache Features.
 *
 * @package redaxo\core
 */
class rex_response
{
    const HTTP_OK = '200 OK';
    const HTTP_MOVED_PERMANENTLY = '301 Moved Permanently';
    const HTTP_NOT_MODIFIED = '304 Not Modified';
    const HTTP_MOVED_TEMPORARILY = '307 Temporary Redirect';
    const HTTP_NOT_FOUND = '404 Not Found';
    const HTTP_FORBIDDEN = '403 Forbidden';
    const HTTP_UNAUTHORIZED = '401 Unauthorized';
    const HTTP_INTERNAL_ERROR = '500 Internal Server Error';
    const HTTP_SERVICE_UNAVAILABLE = '503 Service Unavailable';

    private static $httpStatus = self::HTTP_OK;
    private static $sentLastModified = false;
    private static $sentEtag = false;
    private static $sentContentType = false;
    private static $sentCacheControl = false;
    private static $additionalHeaders = [];
    private static $preloadFiles = [];

    /**
     * Sets the HTTP Status code.
     *
     * @param int $httpStatus
     *
     * @throws InvalidArgumentException
     */
    public static function setStatus($httpStatus)
    {
        if (strpos($httpStatus, "\n") !== false) {
            throw new InvalidArgumentException('Illegal http-status "' . $httpStatus . '", contains newlines');
        }

        self::$httpStatus = $httpStatus;
    }

    /**
     * Returns the HTTP Status code.
     *
     * @return string
     */
    public static function getStatus()
    {
        return self::$httpStatus;
    }

    /**
     * Set a http response header. A existing header with the same name will be overridden.
     *
     * @param string $name
     * @param string $value
     */
    public static function setHeader($name, $value)
    {
        self::$additionalHeaders[$name] = $value;
    }

    private static function sendAdditionalHeaders()
    {
        foreach (self::$additionalHeaders as $name => $value) {
            header($name .': ' . $value);
        }
    }

    /**
     * Set a file to be preload via http link header.
     *
     * @param string $file
     * @param string $type
     * @param string $mimeType
     */
    public static function preload($file, $type, $mimeType)
    {
        self::$preloadFiles[] = [
            'file' => $file,
            'type' => $type,
            'mimeType' => $mimeType,
        ];
    }

    private static function sendPreloadHeaders()
    {
        foreach (self::$preloadFiles as $preloadFile) {
            header('Link: <' . $preloadFile['file'] . '>; rel=preload; as=' . $preloadFile['type'] . '; type="' . $preloadFile['mimeType'].'"; crossorigin; nopush', false);
        }
    }

    /**
     * Redirects to a URL.
     *
     * NOTE: Execution will stop within this method!
     *
     * @param string $url URL
     *
     * @throws InvalidArgumentException
     */
    public static function sendRedirect($url)
    {
        if (strpos($url, "\n") !== false) {
            throw new InvalidArgumentException('Illegal redirect url "' . $url . '", contains newlines');
        }

        self::cleanOutputBuffers();
        self::sendAdditionalHeaders();
        self::sendPreloadHeaders();

        header('HTTP/1.1 ' . self::$httpStatus);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Sends a file to client.
     *
     * @param string      $file               File path
     * @param string      $contentType        Content type
     * @param string      $contentDisposition Content disposition ("inline" or "attachment")
     * @param null|string $filename           Custom Filename
     */
    public static function sendFile($file, $contentType, $contentDisposition = 'inline', $filename = null)
    {
        self::cleanOutputBuffers();

        if (!file_exists($file)) {
            header('HTTP/1.1 ' . self::HTTP_NOT_FOUND);
            exit;
        }

        // prevent session locking while sending huge files
        session_write_close();

        if (!$filename) {
            $filename = basename($file);
        }

        self::sendContentType($contentType);
        header('Content-Disposition: ' . $contentDisposition . '; filename="' . $filename . '"');

        self::sendLastModified(filemtime($file));

        header('HTTP/1.1 ' . self::$httpStatus);
        if (!self::$sentCacheControl) {
            self::sendCacheControl('max-age=3600, must-revalidate, proxy-revalidate, private');
        }

        // content length schicken, damit der browser einen ladebalken anzeigen kann
        if (!ini_get('zlib.output_compression')) {
            header('Content-Length: ' . filesize($file));
        }

        self::sendAdditionalHeaders();
        self::sendPreloadHeaders();

        readfile($file);
    }

    /**
     * Sends a resource to the client.
     *
     * @param string      $content            Content
     * @param null|string $contentType        Content type
     * @param null|int    $lastModified       HTTP Last-Modified Timestamp
     * @param null|string $etag               HTTP Cachekey to identify the cache
     * @param null|string $contentDisposition Content disposition ("inline" or "attachment")
     * @param null|string $filename           Filename
     */
    public static function sendResource($content, $contentType = null, $lastModified = null, $etag = null, $contentDisposition = null, $filename = null)
    {
        if ($contentDisposition) {
            header('Content-Disposition: ' . $contentDisposition . '; filename="' . $filename . '"');
        }

        self::sendCacheControl('max-age=3600, must-revalidate, proxy-revalidate, private');
        self::sendContent($content, $contentType, $lastModified, $etag);
    }

    /**
     * Sends a page to client.
     *
     * The page content can be modified by the Extension Point OUTPUT_FILTER
     *
     * @param string $content      Content of page
     * @param int    $lastModified HTTP Last-Modified Timestamp
     */
    public static function sendPage($content, $lastModified = null)
    {
        // ----- EXTENSION POINT
        $content = rex_extension::registerPoint(new rex_extension_point('OUTPUT_FILTER', $content));

        $hasShutdownExtension = rex_extension::isRegistered('RESPONSE_SHUTDOWN');
        if ($hasShutdownExtension) {
            header('Connection: close');
        }

        self::sendContent($content, null, $lastModified);

        // ----- EXTENSION POINT - (read only)
        if ($hasShutdownExtension) {
            // unlock session
            session_write_close();

            rex_extension::registerPoint(new rex_extension_point('RESPONSE_SHUTDOWN', $content, [], true));
        }
    }

    /**
     * Sends content to the client.
     *
     * @param string $content      Content
     * @param string $contentType  Content type
     * @param int    $lastModified HTTP Last-Modified Timestamp
     * @param string $etag         HTTP Cachekey to identify the cache
     */
    public static function sendContent($content, $contentType = null, $lastModified = null, $etag = null)
    {
        if (!self::$sentContentType) {
            self::sendContentType($contentType);
        }
        if (!self::$sentCacheControl) {
            self::sendCacheControl();
        }

        $environment = rex::isBackend() ? 'backend' : 'frontend';

        if (
            self::$httpStatus == self::HTTP_OK &&
            // Safari incorrectly caches 304s as empty pages, so don't serve it 304s
            // http://tech.vg.no/2013/10/02/ios7-bug-shows-white-page-when-getting-304-not-modified-from-server/
            // https://bugs.webkit.org/show_bug.cgi?id=32829
            (!empty($_SERVER['HTTP_USER_AGENT']) && (false === strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') || false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')))
        ) {
            // ----- Last-Modified
            if (!self::$sentLastModified
                && (rex::getProperty('use_last_modified') === true || rex::getProperty('use_last_modified') === $environment)
            ) {
                self::sendLastModified($lastModified);
            }

            // ----- ETAG
            if (!self::$sentEtag
                && (rex::getProperty('use_etag') === true || rex::getProperty('use_etag') === $environment)
            ) {
                self::sendEtag($etag ?: self::md5($content));
            }
        }

        // ----- GZIP
        if (rex::getProperty('use_gzip') === true || rex::getProperty('use_gzip') === $environment) {
            $content = self::sendGzip($content);
        }

        self::cleanOutputBuffers();

        header('HTTP/1.1 ' . self::$httpStatus);

        // content length schicken, damit der browser einen ladebalken anzeigen kann
        header('Content-Length: ' . rex_string::size($content));

        self::sendAdditionalHeaders();
        self::sendPreloadHeaders();

        echo $content;

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    /**
     * Cleans all output buffers.
     */
    public static function cleanOutputBuffers()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    /**
     * Sends the content type header.
     *
     * @param string $contentType
     */
    public static function sendContentType($contentType = null)
    {
        header('Content-Type: ' . ($contentType ?: 'text/html; charset=utf-8'));
        self::$sentContentType = true;
    }

    /**
     * Sends the cache control header.
     */
    public static function sendCacheControl($cacheControl = 'must-revalidate, proxy-revalidate, private, no-cache, max-age=0')
    {
        header('Cache-Control: ' . $cacheControl);
        self::$sentCacheControl = true;
    }

    /**
     * Checks if content has changed by the last modified timestamp.
     *
     * HTTP_IF_MODIFIED_SINCE feature
     *
     * @param int $lastModified HTTP Last-Modified Timestamp
     */
    public static function sendLastModified($lastModified = null)
    {
        if (!$lastModified) {
            $lastModified = time();
        }

        $lastModified = gmdate('D, d M Y H:i:s T', (float) $lastModified);

        // Sende Last-Modification time
        header('Last-Modified: ' . $lastModified);

        // Last-Modified Timestamp gefunden
        // => den Browser anweisen, den Cache zu verwenden
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lastModified) {
            self::cleanOutputBuffers();

            header('HTTP/1.1 ' . self::HTTP_NOT_MODIFIED);
            exit;
        }
        self::$sentLastModified = true;
    }

    /**
     * Checks if content has changed by the etag cachekey.
     *
     * HTTP_IF_NONE_MATCH feature
     *
     * @param string $cacheKey HTTP Cachekey to identify the cache
     */
    public static function sendEtag($cacheKey)
    {
        // Laut HTTP Spec muss der Etag in " sein
        $cacheKey = '"' . $cacheKey . '"';

        // Sende CacheKey als ETag
        header('ETag: ' . $cacheKey);

        // CacheKey gefunden
        // => den Browser anweisen, den Cache zu verwenden
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $cacheKey) {
            self::cleanOutputBuffers();

            header('HTTP/1.1 ' . self::HTTP_NOT_MODIFIED);
            exit;
        }
        self::$sentEtag = true;
    }

    /**
     * Encodes the content with GZIP/X-GZIP if the browser supports one of them.
     *
     * HTTP_ACCEPT_ENCODING feature
     *
     * @param string $content Content
     *
     * @return string
     */
    protected static function sendGzip($content)
    {
        $enc = '';
        $encodings = [];
        $supportsGzip = false;

        // Check if it supports gzip
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $encodings = explode(',', strtolower(preg_replace('/\s+/', '', $_SERVER['HTTP_ACCEPT_ENCODING'])));
        }

        if ((in_array('gzip', $encodings) || in_array('x-gzip', $encodings) || isset($_SERVER['---------------']))
            && function_exists('ob_gzhandler')
            && !ini_get('zlib.output_compression')
        ) {
            $enc = in_array('x-gzip', $encodings) ? 'x-gzip' : 'gzip';
            $supportsGzip = true;
        }

        if ($supportsGzip) {
            header('Content-Encoding: ' . $enc);
            $content = gzencode($content, 9, FORCE_GZIP);
        }

        return $content;
    }

    // method inspired by https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/Cookie.php

    /**
     * @param string      $name    The name of the cookie
     * @param string|null $value   The value of the cookie, a empty value to delete the cookie.
     * @param array       $options Different cookie Options. Supported keys are:
     *                             "expires" int|string|\DateTimeInterface The time the cookie expires
     *                             "path" string                           The path on the server in which the cookie will be available on
     *                             "domain" string|null                    The domain that the cookie is available to
     *                             "secure" bool                           Whether the cookie should only be transmitted over a secure HTTPS connection from the client
     *                             "httponly" bool                         Whether the cookie will be made accessible only through the HTTP protocol
     *                             "samesite" string|null                  Whether the cookie will be available for cross-site requests
     *                             "raw" bool                              Whether the cookie value should be sent with no url encoding
     *
     * @throws \InvalidArgumentException
     */
    public static function sendCookie($name, $value, array $options = [])
    {
        $expire = isset($options['expires']) ? $options['expires'] : 0;
        $path = isset($options['path']) ? $options['path'] : '/';
        $domain = isset($options['domain']) ? $options['domain'] : null;
        $secure = isset($options['secure']) ? $options['secure'] : false;
        $httpOnly = isset($options['httponly']) ? $options['httponly'] : true;
        $sameSite = isset($options['samesite']) ? $options['samesite'] : null;
        $raw = isset($options['raw']) ? $options['raw'] : false;

        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        }
        // convert expiration time to a Unix timestamp
        if ($expire instanceof \DateTimeInterface) {
            $expire = $expire->format('U');
        } elseif (!is_numeric($expire)) {
            $expire = strtotime($expire);
            if (false === $expire) {
                throw new \InvalidArgumentException('The cookie expiration time is not valid.');
            }
        }

        $expire = 0 < $expire ? (int) $expire : 0;
        $maxAge = $expire - time();
        $maxAge = 0 >= $maxAge ? 0 : $maxAge;
        $path = empty($path) ? '/' : $path;

        if (null !== $sameSite) {
            $sameSite = strtolower($sameSite);
        }
        if (!in_array($sameSite, ['lax', 'strict', null], true)) {
            throw new \InvalidArgumentException('The "sameSite" parameter value is not valid.');
        }

        $str = 'Set-Cookie: '. ($raw ? $name : urlencode($name)).'=';
        if ('' === (string) $value) {
            $str .= 'deleted; expires='.gmdate('D, d-M-Y H:i:s T', time() - 31536001).'; Max-Age=0';
        } else {
            $str .= $raw ? $value : rawurlencode($value);
            if (0 !== $expire) {
                $str .= '; expires='.gmdate('D, d-M-Y H:i:s T', $expire).'; Max-Age='.$maxAge;
            }
        }
        if ($path) {
            $str .= '; path='.$path;
        }
        if ($domain) {
            $str .= '; domain='.$domain;
        }
        if ($secure) {
            $str .= '; secure';
        }
        if ($httpOnly) {
            $str .= '; httponly';
        }
        if ($sameSite) {
            $str .= '; samesite='.$sameSite;
        }

        header($str, false);
    }

    /**
     * Creates the md5 checksum for the content.
     *
     * Dynamic content surrounded by `<!--DYN-->…<!--/DYN-->` is ignored.
     *
     * @param string $content
     *
     * @return string
     */
    private static function md5($content)
    {
        return md5(preg_replace('@<!--DYN-->.*<!--/DYN-->@U', '', $content));
    }

    public static function enforceHttps()
    {
        if (!rex_request::isHttps()) {
            self::setStatus(self::HTTP_MOVED_PERMANENTLY);
            self::sendRedirect('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        }
    }
}
