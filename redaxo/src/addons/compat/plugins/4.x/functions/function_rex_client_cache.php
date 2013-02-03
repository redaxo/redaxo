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
function rex_send_resource($content, $sendcharset = true, $lastModified = null, $etag = null)
{
  rex_response::sendResource($content, $sendcharset, $lastModified, $etag);
}

/**
 * @see rex_response::sendArticle()
 *
 * @deprecated 5.0
 */
function rex_send_article($REX_ARTICLE, $content, $environment, $sendcharset = false)
{
  rex_response::sendArticle($content);
}

/**
 * @see rex_response::sendContent()
 *
 * @deprecated 5.0
 */
function rex_send_content($content, $lastModified, $etag, $environment, $sendcharset = false)
{
  rex_response::sendContent($content, $lastModified, $etag, $environment, $sendcharset);
}
