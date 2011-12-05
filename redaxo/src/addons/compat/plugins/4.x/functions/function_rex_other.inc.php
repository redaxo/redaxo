<?php

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

  if($html === true)
  {
    return ' tabindex="'. ++$REX['TABINDEX'] .'"';
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
  if(rex::getUser()->hasPerm('accesskeys[]'))
    return ' accesskey="'. $key .'" title="'. $title .' ['. $key .']"';

  return ' title="'. $title .'"';
}

/**
 * @deprecated 5.0
 */
function array_insert($array, $index, $value)
{
  // In PHP5 akzeptiert array_merge nur arrays. Deshalb hier $value als Array verpacken
  return array_merge(array_slice($array, 0, $index), array($value), array_slice($array, $index));
}