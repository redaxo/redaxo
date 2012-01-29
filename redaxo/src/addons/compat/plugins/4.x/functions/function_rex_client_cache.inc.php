<?php

/**
 * @see rex_response::sendFile()
 *
 * @deprecated 5.0
 */
function rex_send_file($file, $contentType, $environment = 'backend')
{
  rex_response::sendFile($file, $contentType);
}

/**
 * @see rex_response::sendResource()
 *
 * @deprecated 5.0
 */
function rex_send_resource($content, $sendcharset = TRUE, $lastModified = null, $etag = null)
{
  rex_response::sendResource($content, $sendcharset, $lastModified, $etag);
}

/**
 * @see rex_response::sendArticle()
 *
 * @deprecated 5.0
 */
function rex_send_article($REX_ARTICLE, $content, $environment, $sendcharset = FALSE)
{
  rex_response::sendArticle($content);
}

/**
 * @see rex_response::sendContent()
 *
 * @deprecated 5.0
 */
function rex_send_content($content, $lastModified, $etag, $environment, $sendcharset = FALSE)
{
  rex_response::sendContent($content, $lastModified, $etag, $environment, $sendcharset);
}

/**
 * @see rex_response::sendLastModified()
 *
 * @deprecated 5.0
 */
function rex_send_last_modified($lastModified = null)
{
  rex_response::sendLastModified($lastModified);
}

/**
 * @see rex_response::sendEtag()
 *
 * @deprecated 5.0
 */
function rex_send_etag($cacheKey)
{
  rex_response::sendEtag($cacheKey);
}

/**
 * @see rex_response::sendGzip()
 *
 * @deprecated 5.0
 */
function rex_send_gzip($content)
{
  return rex_response::sendGzip($content);
}

/**
 * @see rex_response::sendChecksum()
 *
 * @deprecated 5.0
 */
function rex_send_checksum($md5)
{
  rex_response::sendChecksum($md5);
}