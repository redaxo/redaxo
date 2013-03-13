<?php

/**
 * HTTP1.1 Client Cache Features
 *
 * @package redaxo\core
 */
class rex_response
{
    const
        HTTP_OK = '200 OK',
        HTTP_MOVED_PERMANENTLY = '301 Moved Permanently',
        HTTP_NOT_MODIFIED = '304 Not Modified',
        HTTP_NOT_FOUND = '404 Not Found',
        HTTP_FORBIDDEN = '403 Forbidden',
        HTTP_UNAUTHORIZED = '401 Unauthorized',
        HTTP_INTERNAL_ERROR = '500 Internal Server Error';

    private static
        $httpStatus = self::HTTP_OK,
        $sentLastModified = false,
        $sentEtag = false,
        $sentContentType = false;

    public static function setStatus($httpStatus)
    {
        if (strpos($httpStatus, "\n") !== false) {
            throw new InvalidArgumentException('Illegal http-status "' . $httpStatus . '", contains newlines');
        }

        self::$httpStatus = $httpStatus;
    }

    public static function getStatus()
    {
        return self::$httpStatus;
    }

    /**
     * Redirects to a URL
     *
     * @param string $url URL
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
     * Sends a file to client
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

        // ----- MD5 Checksum
        $environment = rex::isBackend() ? 'backend' : 'frontend';
        if (rex::getProperty('use_md5') === true || rex::getProperty('use_md5') === $environment) {
            self::sendChecksum(md5_file($file));
        }

        header('HTTP/1.1 ' . self::$httpStatus);
        self::sendCacheControl();

        // content length schicken, damit der browser einen ladebalken anzeigen kann
        header('Content-Length: ' . filesize($file));

        readfile($file);
    }

    /**
     * Sends a page to client
     *
     * The page content can be modified by the Extension Point OUTPUT_FILTER
     *
     * @param string  $content      Content of page
     * @param integer $lastModified HTTP Last-Modified Timestamp
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
     * Sends content to the client
     *
     * @param string  $content      Content
     * @param string  $contentType  Content type
     * @param integer $lastModified HTTP Last-Modified Timestamp
     * @param string  $etag         HTTP Cachekey to identify the cache
     */
    public static function sendContent($content, $contentType = null, $lastModified = null, $etag = null)
    {
        if (!self::$sentContentType) {
            self::sendContentType($contentType);
        }

        $environment = rex::isBackend() ? 'backend' : 'frontend';

        if (self::$httpStatus == self::HTTP_OK) {
            // ----- Last-Modified
            if (!self::$sentLastModified
                && rex::getProperty('use_last_modified') === true || rex::getProperty('use_last_modified') === $environment
            ) {
                self::sendLastModified($lastModified);
            }

            // ----- ETAG
            if (!self::$sentEtag
                && rex::getProperty('use_etag') === true || rex::getProperty('use_etag') === $environment
            ) {
                self::sendEtag($etag ?: self::md5($content));
            }
        }

        // ----- GZIP
        if (rex::getProperty('use_gzip') === true || rex::getProperty('use_gzip') === $environment) {
            $content = self::sendGzip($content);
        }

        // ----- MD5 Checksum
        if (rex::getProperty('use_md5') === true || rex::getProperty('use_md5') === $environment) {
            self::sendChecksum(self::md5($content));
        }

        self::cleanOutputBuffers();

        header('HTTP/1.1 ' . self::$httpStatus);
        self::sendCacheControl();

        // content length schicken, damit der browser einen ladebalken anzeigen kann
        header('Content-Length: ' . rex_string::size($content));

        echo $content;
    }

    /**
     * Cleans all output buffers
     */
    public static function cleanOutputBuffers()
    {
        while (ob_get_length()) {
            ob_end_clean();
        }
    }

    /**
     * Sends the content type header
     *
     * @param string $contentType
     */
    public static function sendContentType($contentType = null)
    {
        header('Content-Type: ' . ($contentType ?: 'text/html; charset=utf-8'));
        self::$sentContentType = true;
    }

    /**
     * Sends the cache control header
     */
    public static function sendCacheControl()
    {
        header('Cache-Control: must-revalidate, proxy-revalidate, private');
    }

    /**
     * Checks if content has changed by the last modified timestamp
     *
     * HTTP_IF_MODIFIED_SINCE feature
     *
     * @param integer $lastModified HTTP Last-Modified Timestamp
     */
    public static function sendLastModified($lastModified = null)
    {
        if (!$lastModified)
            $lastModified = time();

        $lastModified = date('r', (float) $lastModified);

        // Sende Last-Modification time
        header('Last-Modified: ' . $lastModified);

        // Last-Modified Timestamp gefunden
        // => den Browser anweisen, den Cache zu verwenden
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lastModified) {
            self::cleanOutputBuffers();

            header('HTTP/1.1 ' . self::HTTP_NOT_MODIFIED);
            self::sendCacheControl();
            exit;
        }
        self::$sentLastModified = true;
    }

    /**
     * Checks if content has changed by the etag cachekey
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
            self::sendCacheControl();
            exit;
        }
        self::$sentEtag = true;
    }

    /**
     * Encodes the content with GZIP/X-GZIP if the browser supports one of them
     *
     * HTTP_ACCEPT_ENCODING feature
     *
     * @param string $content Content
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
     * Sends a MD5 checksum as HTTP header, so the browser can validate the output
     *
     * HTTP_CONTENT_MD5 feature
     *
     * @param string $md5 MD5 Checksum
     */
    protected static function sendChecksum($md5)
    {
        header('Content-MD5: ' . $md5);
    }

    private static function md5($content)
    {
        return md5(preg_replace('@<!--DYN-->.*<!--/DYN-->@U', '', $content));
    }
}
