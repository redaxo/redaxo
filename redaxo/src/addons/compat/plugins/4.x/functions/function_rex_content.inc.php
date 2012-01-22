<?php

/**
 * @see rex_content_service::moveSlice()
 *
 * @deprecated 5.0
 */
function rex_moveSliceUp($slice_id, $clang)
{
  return rex_moveSlice($slice_id, $clang, 'moveup');
}

/**
 * @see rex_content_service::moveSlice()
 *
 * @deprecated 5.0
 */
function rex_moveSliceDown($slice_id, $clang)
{
  return rex_moveSlice($slice_id, $clang, 'movedown');
}

/**
 * @see rex_content_service::moveSlice()
 *
 * @deprecated 5.0
 */
function rex_moveSlice($slice_id, $clang, $direction)
{
  $success = false;
  try {
    $message = rex_content_service::moveSlice($slice_id, $clang, $direction);
    $success = true;
  } catch (rex_api_exception $e)
  {
    $message = $e->getMessage();
  }
  return array($success, $message);
}

/**
 * @see rex_content_service::deleteSlice()
 *
 * @deprecated 5.0
 */
function rex_deleteSlice($slice_id)
{
  return rex_content_service::deleteSlice($slice_id);
}

/**
 * @see rex_content_service::copyCategory()
 *
 * @deprecated 5.0
 */
function rex_copyCategory($from_cat, $to_cat)
{
  return rex_content_service::copyCategory($from_cat, $to_cat);
}

/**
 * @see rex_content_service::copyMeta()
 *
 * @deprecated 5.0
 */
function rex_copyMeta($from_id, $to_id, $from_clang = 0, $to_clang = 0, $params = array ())
{
  return rex_content_service::copyMeta($from_id, $to_id, $from_clang, $to_clang, $params);
}

/**
 * @see rex_content_service::copyContent()
 *
 * @deprecated 5.0
 */
function rex_copyContent($from_id, $to_id, $from_clang = 0, $to_clang = 0, $from_re_sliceid = 0, $revision = 0)
{
  return rex_content_service::copyContent($from_id, $to_id, $from_clang, $to_clang, $from_re_sliceid, $revision);
}

/**
 * @see rex_content_service::copyArticle()
 *
 * @deprecated 5.0
 */
function rex_copyArticle($id, $to_cat_id)
{
  return rex_content_service::copyArticle($id, $to_cat_id);
}

/**
 * @see rex_content_service::moveArticle()
 *
 * @deprecated 5.0
 */
function rex_moveArticle($id, $from_cat_id, $to_cat_id)
{
  return rex_content_service::moveArticle($id, $from_cat_id, $to_cat_id);
}

/**
 * @see rex_content_service::moveCategory()
 *
 * @deprecated 5.0
 */
function rex_moveCategory($from_cat, $to_cat)
{
  return rex_content_service::moveCategory($from_cat, $to_cat);
}

/**
 * @see rex_content_service::generateArticleContent()
 *
 * @deprecated 5.0
 */
function rex_generateArticleContent($article_id, $clang = null)
{
  return rex_content_service::generateArticleContent($article_id, $clang);
}