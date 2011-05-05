<?php
/**
 * Gibt den nächsten freien Tabindex zurück.
 * Der Tabindex ist eine stetig fortlaufende Zahl,
 * welche die Priorität der Tabulatorsprünge des Browsers regelt.
 *
 * @return integer nächster freier Tabindex
 * @deprecated since 5.0
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