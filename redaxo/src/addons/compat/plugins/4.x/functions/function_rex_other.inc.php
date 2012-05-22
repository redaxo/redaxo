<?php

/**
 * @see rex_deleteCache()
 *
 * @deprecated 5.0
 */
function rex_generateAll()
{
  $MSG = rex_deleteCache();
  // ----- EXTENSION POINT
  $MSG = rex_extension::registerPoint('ALL_GENERATED', $MSG);
  return $MSG;
}

/**
 * @see rex_deleteCache()
 *
 * @deprecated 5.0
 */
function rex_deleteAll()
{
  rex_deleteCache();
}

/**
 * @deprecated 5.0
 */
function rex_tabindex($html = true)
{
  global $REX;

  if (empty($REX['TABINDEX']))
  {
    $REX['TABINDEX'] = 0;
  }

  if ($html === true)
  {
    return ' tabindex="' . ++$REX['TABINDEX'] . '"';
  }
  return ++$REX['TABINDEX'];
}

/**
 * @see rex_sql_dump::import()
 *
 * @deprecated 5.0
 */
function rex_install_dump($file, $debug = false)
{
  return rex_sql_dump::import($file, $debug);
}

/**
 * @see rex::getAccesskey()
 *
 * @deprecated 5.0
 */
function rex_accesskey($title, $key)
{
  if (rex::getProperty('use_accesskeys'))
    return ' accesskey="' . $key . '" title="' . $title . ' [' . $key . ']"';

  return ' title="' . $title . '"';
}

/**
 * @deprecated 5.0
 */
function array_insert($array, $index, $value)
{
  // In PHP5 akzeptiert array_merge nur arrays. Deshalb hier $value als Array verpacken
  return array_merge(array_slice($array, 0, $index), array($value), array_slice($array, $index));
}

/**
 * @see rex_backend_login::hasSession()
 *
 * @deprecated 5.0
 */
function rex_hasBackendSession()
{
  return rex_backend_login::hasSession();
}

/**
* @see rex_view::info()
*
* @deprecated 5.0
*/
function rex_info($message, $cssClass = null, $sorround_tag = null)
{
  return rex_view::info($message, $cssClass, $sorround_tag);
}

/**
 * @see rex_view::success()
 *
 * @deprecated 5.0
 */
function rex_success($message, $cssClass = null, $sorround_tag = null)
{
  return rex_view::success($message, $cssClass, $sorround_tag);
}

/**
 * @see rex_view::warning()
 *
 * @deprecated 5.0
 */
function rex_warning($message, $cssClass = null, $sorround_tag = null)
{
  return rex_view::warning($message, $cssClass, $sorround_tag);
}

/**
 * @see rex_view::infoBlock()
 *
 * @deprecated 5.0
 */
function rex_info_block($message, $cssClass = null, $sorround_tag = null)
{
  return rex_view::infoBlock($message, $cssClass, $sorround_tag);
}

/**
 * @see rex_view::warningBlock()
 *
 * @deprecated 5.0
 */
function rex_warning_block($message, $cssClass = null, $sorround_tag = null)
{
  return rex_view::warningBlock($message, $cssClass, $sorround_tag);
}

/**
 * @see rex_view::toolbar()
 *
 * @deprecated 5.0
 */
function rex_toolbar($content, $cssClass = null)
{
  return rex_view::toolbar($content, $cssClass);
}

/**
 * @see rex_view::contentBlock()
 *
 * @deprecated 5.0
 */
function rex_content_block($content)
{
  return rex_view::contentBlock($content);
}

/**
* @see rex_view::title()
*
* @deprecated 5.0
*/
function rex_title($head, $subtitle = '')
{
  echo rex_view::title($head, $subtitle);
}

/**
* Escaped einen String
*
* @param $string Zu escapender String
*
* @deprecated 5.0
*/
function rex_addslashes($string, $flag = '\\\'\"')
{
  if ($flag == '\\\'\"')
  {
    $string = str_replace('\\', '\\\\', $string);
    $string = str_replace('\'', '\\\'', $string);
    $string = str_replace('"', '\"', $string);
  }
  elseif ($flag == '\\\'')
  {
    $string = str_replace('\\', '\\\\', $string);
    $string = str_replace('\'', '\\\'', $string);
  }
  return $string;
}

/**
 * @see rex_string::split()
 *
 * @deprecated 5.0
 */
function rex_split_string($string)
{
  return rex_string::split($string);
}

/**
 * @see rex_string::highlight()
 *
 * @deprecated 5.0
 */
function rex_highlight_string($string, $return = false)
{
  $s = rex_string::highlight($string);
  if ($return)
  {
    return $s;
  }
  echo $s;
}

/**
 * @see rex_string::highlight()
 *
* @deprecated 5.0
*/
function rex_highlight_file($filename, $return = false)
{
  $s = '<p class="rex-code">' . highlight_file($filename, true) . '</p>';
  if ($return)
  {
    return $s;
  }
  echo $s;
}

/**
 * @see rex_request::isXmlHttpRequest()
 *
 * @deprecated 5.0
 */
function rex_isXmlHttpRequest()
{
  return rex_request::isXmlHttpRequest();
}

/**
* @see rex_sql_util::organizePriorities()
*
* @deprecated 5.0
*/
function rex_organize_priorities($tableName, $priorColumnName, $whereCondition = '', $orderBy = '', $id_field = 'id', $startBy = 1)
{
  rex_sql_util::organizePriorities($tableName, $priorColumnName, $whereCondition, $orderBy, $id_field, $startBy);
}

/**
 * @see rex_sql::getArrayValue()
 *
 * @deprecated 5.0
 */
function rex_getAttributes($name, $content, $default = null)
{
  $prop = unserialize($content, true);
  if (isset($prop[$name])) return $prop[$name];
  return $default;
}

/**
 * @see rex_sql::setArrayValue()
 *
 * @deprecated 5.0
 */
function rex_setAttributes($name, $value, $content)
{
  $prop = unserialize($content);
  $prop[$name] = $value;
  return serialize($prop);
}
