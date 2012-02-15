<?php

/**
 * HTTP1.1 Client Cache Features
 *
 * @package redaxo5
 */
class rex_response
{
  /**
   * Sendet eine Datei zum Client
   *
   * @param $file string Pfad zur Datei
   * @param $contentType HTTP ContentType der Datei
   */
  static public function sendFile($file, $contentType)
  {
    $environment = rex::isBackend() ? 'backend' : 'frontend';

    // Cachen für Dateien aktivieren
    $temp = rex::getProperty('use_last_modified');
    rex::setProperty('use_last_modified', true);

    header('Content-Type: '. $contentType);
    header('Content-Disposition: inline; filename="'.basename($file).'"');

    $content = rex_file::get($file);
    $cacheKey = md5($content . $file . $contentType . $environment);

    self::sendContent(
      $content,
      filemtime($file),
      $cacheKey,
      $environment);

    // Setting zurücksetzen
    rex::setProperty('use_last_modified', $temp);
  }

  /**
   * Sendet eine ressource zum Client,
   * fügt ggf. HTTP1.1 cache headers hinzu
   *
   * @param $content string Inhalt der Ressource
   * @param $sendcharset boolean TRUE, wenn der Charset mitgeschickt werden soll, sonst FALSE
   * @param $lastModified integer HTTP Last-Modified Timestamp
   * @param $etag string Cachekey zur identifizierung des Caches
   */
  static public function sendResource($content, $sendcharset = TRUE, $lastModified = null, $etag = null)
  {
    $environment = rex::isBackend() ? 'backend' : 'frontend';

    if(!$etag)
    {
      $etag = md5($content);
    }
    if(!$lastModified)
    {
      $lastModified = time();
    }

    self::sendContent($content, $lastModified, $etag, $environment, $sendcharset);
  }

  /**
   * Sendet einen rex_article zum Client,
   * fügt ggf. HTTP1.1 cache headers hinzu
   *
   * @param $content string Inhalt des Artikels
   * @param $lastModified integer HTTP Last-Modified Timestamp
   */
  static public function sendArticle($content, $lastModified = null, $etagAdd = '')
  {
    $environment = rex::isBackend() ? 'backend' : 'frontend';
    $sendcharset = TRUE;

    // ----- EXTENSION POINT
    $content = rex_extension::registerPoint( 'OUTPUT_FILTER', $content, array('environment' => $environment,'sendcharset' => $sendcharset));

    // dynamische teile sollen die md5 summe nicht beeinflussen
    $etag = self::md5($content . $etagAdd);

    if($lastModified === null)
    {
      $lastModified = time();
    }

    self::sendContent(
      $content,
      $lastModified,
      $etag,
      $environment,
      $sendcharset);

    // ----- EXTENSION POINT - (read only)
    rex_extension::registerPoint( 'OUTPUT_FILTER_CACHE', $content, array(), true);
  }

  /**
   * Sendet den Content zum Client,
   * fügt ggf. HTTP1.1 cache headers hinzu
   *
   * @param $content string Inhalt des Artikels
   * @param $lastModified integer HTTP Last-Modified Timestamp
   * @param $etag string HTTP Cachekey zur identifizierung des Caches
   * @param $environment string Die Umgebung aus der der Inhalt gesendet wird
   * (frontend/backend)
   * @param $sendcharset boolean TRUE, wenn der Charset mitgeschickt werden soll, sonst FALSE
   */
  static public function sendContent($content, $lastModified, $etag, $environment, $sendcharset = FALSE)
  {
    // Cachen erlauben, nach revalidierung
    // see http://xhtmlforum.de/35221-php-session-etag-header.html#post257967
    session_cache_limiter('none');
    header('Cache-Control: must-revalidate, proxy-revalidate, private');

    if($sendcharset)
    {
      header('Content-Type: text/html; charset=utf-8');
    }

    // ----- Last-Modified
    if(rex::getProperty('use_last_modified') === 'true' || rex::getProperty('use_last_modified') == $environment)
      self::sendLastModified($lastModified);

    // ----- ETAG
    if(rex::getProperty('use_etag') === 'true' || rex::getProperty('use_etag') == $environment)
      self::sendEtag($etag);

    // ----- GZIP
    if(rex::getProperty('use_gzip') === 'true' || rex::getProperty('use_gzip') == $environment)
      $content = self::sendGzip($content);

    // ----- MD5 Checksum
    // dynamische teile sollen die md5 summe nicht beeinflussen
    if(rex::getProperty('use_md5') === 'true' || rex::getProperty('use_md5') == $environment)
      self::sendChecksum(self::md5($content));

    // content length schicken, damit der browser einen ladebalken anzeigen kann
    header('Content-Length: '. rex_string::size($content));

    echo $content;
  }

  /**
   * Prüft, ob sich dateien geändert haben
   *
   * XHTML 1.1: HTTP_IF_MODIFIED_SINCE feature
   *
   * @param $lastModified integer HTTP Last-Modified Timestamp
   */
  static public function sendLastModified($lastModified = null)
  {
    if(!$lastModified)
      $lastModified = time();

    $lastModified = date('r', (float) $lastModified);

    // Sende Last-Modification time
    header('Last-Modified: ' . $lastModified);

    // Last-Modified Timestamp gefunden
    // => den Browser anweisen, den Cache zu verwenden
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lastModified)
    {
      if(ob_get_length() > 0)
        while(@ob_end_clean());

      header('HTTP/1.1 304 Not Modified');
      exit();
    }
  }

  /**
   * Prüft ob sich der Inhalt einer Seite im Cache des Browsers befindet und
   * verweisst ggf. auf den Cache
   *
   * XHTML 1.1: HTTP_IF_NONE_MATCH feature
   *
   * @param $cacheKey string HTTP Cachekey zur identifizierung des Caches
   */
  static public function sendEtag($cacheKey)
  {
    // Laut HTTP Spec muss der Etag in " sein
    $cacheKey = '"'. $cacheKey .'"';

    // Sende CacheKey als ETag
    header('ETag: '. $cacheKey);

    // CacheKey gefunden
    // => den Browser anweisen, den Cache zu verwenden
    if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $cacheKey)
    {
      if(ob_get_length() > 0)
        while(@ob_end_clean());

      header('HTTP/1.1 304 Not Modified');
      exit();
    }
  }

  /**
   * Kodiert den Inhalt des Artikels in GZIP/X-GZIP, wenn der Browser eines der
   * Formate unterstützt
   *
   * XHTML 1.1: HTTP_ACCEPT_ENCODING feature
   *
   * @param $content string Inhalt des Artikels
   */
  static public function sendGzip($content)
  {
    $enc = '';
    $encodings = array();
    $supportsGzip = false;

    // Check if it supports gzip
    if (isset($_SERVER['HTTP_ACCEPT_ENCODING']))
    {
      $encodings = explode(',', strtolower(preg_replace('/\s+/', '', $_SERVER['HTTP_ACCEPT_ENCODING'])));
    }else
    {
      $encodings = array();
    }

    if ((in_array('gzip', $encodings) || in_array('x-gzip', $encodings) || isset($_SERVER['---------------'])) && function_exists('ob_gzhandler') && !ini_get('zlib.output_compression'))
    {
      $enc = in_array('x-gzip', $encodings) ? 'x-gzip' : 'gzip';
      $supportsGzip = true;
    }

    if($supportsGzip)
    {
      header('Content-Encoding: '. $enc);
      $content = gzencode($content, 9, FORCE_GZIP);
    }

    return $content;
  }

  /**
   * Sendet eine MD5 Checksumme als HTTP Header, damit der Browser validieren
   * kann, ob Übertragungsfehler aufgetreten sind
   *
   * XHTML 1.1: HTTP_CONTENT_MD5 feature
   *
   * @param $md5 string MD5 Summe des Inhalts
   */
  static public function sendChecksum($md5)
  {
    header('Content-MD5: '. $md5);
  }

  static private function md5($content)
  {
    return md5(preg_replace('@<!--DYN-->.*<!--/DYN-->@U', '', $content));
  }
}
