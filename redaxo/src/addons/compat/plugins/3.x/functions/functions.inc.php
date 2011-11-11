<?php

/**
 * @see rex_getUrl()
 *
 * @deprecated 3.1
 */
function getUrlByid($id, $clang = "", $params = "")
{
  return rex_getUrl($id, $clang, $params);
}

/**
 * @see rex_title()
 *
 * @deprecated 3.2
 */
function title($head, $subtitle = '', $styleclass = "grey", $width = '770px')
{
  return rex_title($head, $subtitle, $styleclass, $width);
}

/**
 * @see rex_parse_article_name()
 *
 * @deprecated 3.2
 */
function rex_parseArticleName($name)
{
  return rex_parse_article_name($name);
}