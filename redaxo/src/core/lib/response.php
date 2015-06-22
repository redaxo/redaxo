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
    const HTTP_NOT_FOUND = '404 Not Found';
    const HTTP_FORBIDDEN = '403 Forbidden';
    const HTTP_UNAUTHORIZED = '401 Unauthorized';
    const HTTP_INTERNAL_ERROR = '500 Internal Server Error';

    private static $httpStatus = self::HTTP_OK;
    private static $sentLastModified = false;
    private static $sentEtag = false;
    private static $sentContentType = false;
    private static $sentCacheControl = false;

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
     * Redirects to a URL.
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

        header('HTTP/1.1 ' . self::$httpStatus);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Sends a file to client.
     *
     * @param string $file               File path
     * @param string $contentType        Content type
     * @param string $contentDisposition Content disposition
     */
    public static function sendFile($file, $contentType, $contentDisposition = 'inline')
    {
        self::cleanOutputBuffers();

        if (!file_exists($file)) {
            header('HTTP/1.1 ' . self::HTTP_NOT_FOUND);
            exit;
        }

        self::sendContentType($contentType);
        header('Content-Disposition: ' . $contentDisposition . '; filename="' . basename($file) . '"');

        self::sendLastModified(filemtime($file));

        header('HTTP/1.1 ' . self::$httpStatus);
        if (!self::$sentCacheControl) {
            self::sendCacheControl('max-age=3600, must-revalidate, proxy-revalidate, private');
        }

        // content length schicken, damit der browser einen ladebalken anzeigen kann
        if (!ini_get('zlib.output_compression')) {
            header('Content-Length: ' . filesize($file));
        }

        readfile($file);
    }

    /**
     * Sends a resource to the client.
     *
     * @param string $content      Content
     * @param string $contentType  Content type
     * @param int    $lastModified HTTP Last-Modified Timestamp
     * @param string $etag         HTTP Cachekey to identify the cache
     */
    public static function sendResource($content, $contentType = null, $lastModified = null, $etag = null)
    {
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

        self::sendContent($content, null, $lastModified);

        // ----- EXTENSION POINT - (read only)
        rex_extension::registerPoint(new rex_extension_point('RESPONSE_SHUTDOWN', $content, [], true));
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
            false === strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')
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
        while (ob_get_length()) {
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
    public static function sendCacheControl($cacheControl = 'must-revalidate, proxy-revalidate, private')
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

        $lastModified = date('r', (float) $lastModified);

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
}
