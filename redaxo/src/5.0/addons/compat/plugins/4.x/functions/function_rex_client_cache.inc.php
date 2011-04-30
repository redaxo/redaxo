<?php

/**
 * @see rex_response::sendFile()
 *
 * @deprecated 5.0
 */
function rex_send_file($file, $contentType, $environment = 'backend')
{
  global $REX;

  // Cachen für Dateien aktivieren
  $temp = $REX['USE_LAST_MODIFIED'];
  $REX['USE_LAST_MODIFIED'] = true;

  header('Content-Type: '. $contentType);
  header('Content-Disposition: inline; filename="'.basename($file).'"');

  $content = rex_file::get($file);
  $cacheKey = md5($content . $file . $contentType . $environment);

  rex_send_content(
    $content,
    filemtime($file),
    $cacheKey,
    $environment);

  // Setting zurücksetzen
  $REX['USE_LAST_MODIFIED'] = $temp;
}

/**
 * @see rex_response::sendResource()
 *
 * @deprecated 5.0
 */
function rex_send_resource($content, $sendcharset = TRUE, $lastModified = null, $etag = null)
{
  global $REX;

  if(!$etag)
  {
    $etag = md5($content);
  }
  if(!$lastModified)
  {
    $lastModified = time();
  }

  rex_send_content($content, $lastModified, $etag, $REX['REDAXO'] ? 'backend' : 'frontend', $sendcharset);

//  rex_send_content($content, $lastModified, $etag, $environment, $sendcharset = FALSE)

}

/**
 * @see rex_response::sendArticle()
 *
 * @deprecated 5.0
 */
function rex_send_article($REX_ARTICLE, $content, $environment, $sendcharset = FALSE)
{
  global $REX;

  // ----- EXTENSION POINT
  $content = rex_extension::registerPoint( 'OUTPUT_FILTER', $content, array('environment' => $environment,'sendcharset' => $sendcharset));

  // dynamische teile sollen die md5 summe nicht beeinflussen
  $etag = md5(preg_replace('@<!--DYN-->.*<!--/DYN-->@','', $content));

  if($REX_ARTICLE)
  {
    $lastModified = $REX_ARTICLE->getValue('updatedate');
    $etag .= $REX_ARTICLE->getValue('pid');

    $art_id = $REX_ARTICLE->getArticleId();
    if($art_id == $REX['NOTFOUND_ARTICLE_ID'] &&
       $art_id != $REX['START_ARTICLE_ID'])
    {
      header("HTTP/1.0 404 Not Found");
    }
  }
  else
  {
    $lastModified = time();
  }

  rex_send_content(
    $content,
    $lastModified,
    $etag,
    $environment,
    $sendcharset);

  // ----- EXTENSION POINT - (read only)
  rex_extension::registerPoint( 'OUTPUT_FILTER_CACHE', $content, array(), true);
}

/**
 * @see rex_response::sendContent()
 *
 * @deprecated 5.0
 */
function rex_send_content($content, $lastModified, $etag, $environment, $sendcharset = FALSE)
{
  global $REX;

  // Cachen erlauben, nach revalidierung
  // see http://xhtmlforum.de/35221-php-session-etag-header.html#post257967
  session_cache_limiter('none');
  header('Cache-Control: must-revalidate, proxy-revalidate, private');

  if($sendcharset)
  {
    header('Content-Type: text/html; charset=utf-8');
  }

  // ----- Last-Modified
  if($REX['USE_LAST_MODIFIED'] === 'true' || $REX['USE_LAST_MODIFIED'] == $environment)
    rex_send_last_modified($lastModified);

  // ----- ETAG
  if($REX['USE_ETAG'] === 'true' || $REX['USE_ETAG'] == $environment)
    rex_send_etag($etag);

  // ----- GZIP
  if($REX['USE_GZIP'] === 'true' || $REX['USE_GZIP'] == $environment)
    $content = rex_send_gzip($content);

  // ----- MD5 Checksum
  // dynamische teile sollen die md5 summe nicht beeinflussen
  if($REX['USE_MD5'] === 'true' || $REX['USE_MD5'] == $environment)
    rex_send_checksum(md5(preg_replace('@<!--DYN-->.*<!--/DYN-->@','', $content)));

  // content length schicken, damit der browser einen ladebalken anzeigen kann
  header('Content-Length: '. strlen($content));

  echo $content;
}

/**
 * @see rex_response::sendLastModified()
 *
 * @deprecated 5.0
 */
function rex_send_last_modified($lastModified = null)
{
  if(!$lastModified)
    $lastModified = time();

  $lastModified = date('r', $lastModified);

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
 * @see rex_response::sendEtag()
 *
 * @deprecated 5.0
 */
function rex_send_etag($cacheKey)
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
 * @see rex_response::sendGzip()
 *
 * @deprecated 5.0
 */
function rex_send_gzip($content)
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
 * @see rex_response::sendChecksum()
 *
 * @deprecated 5.0
 */
function rex_send_checksum($md5)
{
  header('Content-MD5: '. $md5);
}