<?php

/**
 * Funktionen zur Ausgabe der Titel Leiste und Subnavigation
 * @package redaxo5
 * @version svn:$Id$
 */

/**
 * Prüfen ob ein/e Datei/Ordner beschreibbar ist
 *
 * @access public
 * @param string $item Datei oder Verzeichnis
 * @return mixed true bei Erfolg, sonst Fehlermeldung
 */
function rex_is_writable($item)
{
  return _rex_is_writable_info(_rex_is_writable($item), $item);
}

function _rex_is_writable_info($is_writable, $item = '')
{
  $state = true;
  $key = '';
  switch($is_writable)
  {
    case 1:
    {
      $key = 'setup_012';
      break;
    }
    case 2:
    {
      $key = 'setup_014';
      break;
    }
    case 3:
    {
      $key = 'setup_015';
      break;
    }
  }

  if($key != '')
  {
    $file = '';
    if($item != '')
      $file = '<b>'. $item .'</b>';

    $state = rex_i18n::msg($key, '<span class="rex-error">', '</span>', rex_path::absolute($file));
  }

  return $state;
}

function _rex_is_writable($item)
{
  // Fehler unterdrücken, falls keine Berechtigung
  if (@ is_dir($item))
  {
    if (!@ is_writable($item . '/.'))
    {
      return 1;
    }
  }
  // Fehler unterdrücken, falls keine Berechtigung
  elseif (@ is_file($item))
  {
    if (!@ is_writable($item))
    {
      return 2;
    }
  }
  else
  {
    return 3;
  }

  return 0;
}

/**
 * Get the attribute $name out of $content. if the attribute is not defined $default is returned.
 *
 * @param string $name
 * @param string $content
 * @param mixed $default
 *
 * @return mixed the attribute with $name if existent, otherwise $default
 */
function rex_getAttributes($name,$content,$default = null)
{
  $prop = json_decode($content, true);
  if (isset($prop[$name])) return $prop[$name];
  return $default;
}

/**
 * Set the attribute $name to $value into $content.
 *
 * @param string $name
 * @param string $value
 * @param string $content
 *
 * @return string the encoded content
 */
function rex_setAttributes($name,$value,$content)
{
  $prop = json_decode($content, true);
  $prop[$name] = $value;
  return json_encode($prop);
}



function rex_ini_get($val)
{
  $val = trim(ini_get($val));
  if ($val != '') {
    $last = strtolower($val{strlen($val)-1});
  } else {
    $last = '';
  }
  switch($last) {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
          $val *= 1024;
      case 'm':
          $val *= 1024;
      case 'k':
          $val *= 1024;
  }

  return $val;
}

/**
 * Trennt einen String an Leerzeichen auf.
 * Dabei wird beachtet, dass Strings in " zusammengehören
 */
function rex_split_string($string)
{
  $spacer = '@@@REX_SPACER@@@';
  $result = array();

  // TODO mehrfachspaces hintereinander durch einfachen ersetzen
  $string = ' ' . trim($string) . ' ';

  // Strings mit Quotes heraussuchen
  $pattern = '!(["\'])(.*)\\1!U';
  preg_match_all($pattern, $string, $matches);
  $quoted = isset ($matches[2]) ? $matches[2] : array();

  // Strings mit Quotes maskieren
  $string = preg_replace($pattern, $spacer, $string);

  // ----------- z.b. 4 "av c" 'de f' ghi
  if (strpos($string, '=') === false)
  {
    $parts = explode(' ', $string);
    foreach ($parts as $part)
    {
      if (empty ($part))
        continue;

      if ($part == $spacer)
      {
        $result[] = array_shift($quoted);
      }
      else
      {
        $result[] = $part;
      }
    }
  }
  // ------------ z.b. a=4 b="av c" y='de f' z=ghi
  else
  {
    $parts = explode(' ', $string);
    foreach ($parts as $part)
    {
      if(empty($part))
        continue;

      $variable = explode('=', $part);

      if (empty ($variable[0]) || empty ($variable[1]))
        continue;

      $var_name = $variable[0];
      $var_value = $variable[1];

      if ($var_value == $spacer)
      {
        $var_value = array_shift($quoted);
      }

      $result[$var_name] = $var_value;
    }
  }
  return $result;
}

/**
 * Allgemeine funktion die eine Datenbankspalte fortlaufend durchnummeriert.
 * Dies ist z.B. nützlich beim Umgang mit einer Prioritäts-Spalte
 *
 * @param $tableName String Name der Datenbanktabelle
 * @param $priorColumnName Name der Spalte in der Tabelle, in der die Priorität (Integer) gespeichert wird
 * @param $whereCondition Where-Bedingung zur Einschränkung des ResultSets
 * @param $orderBy Sortierung des ResultSets
 * @param $id_field Name des Primaerschluessels der Tabelle
 * @param $startBy Startpriorität
 */
function rex_organize_priorities($tableName, $priorColumnName, $whereCondition = '', $orderBy = '', $id_field='id', $startBy = 1)
{
  // Datenbankvariable initialisieren
  $qry = 'SET @count='. ($startBy - 1);
  $sql = rex_sql::factory();
  $sql->setQuery($qry);

  // Spalte updaten
  $qry = 'UPDATE '. $tableName .' SET '. $priorColumnName .' = ( SELECT @count := @count +1 )';

  if($whereCondition != '')
    $qry .= ' WHERE '. $whereCondition;

  if($orderBy != '')
    $qry .= ' ORDER BY '. $orderBy;

  $sql->setQuery($qry);
}

function rex_version_compare($version1, $version2, $comparator = null)
{
  $pattern = '/(?<=\d)(?=[a-z])|(?<=[a-z])(?=\d)|[ .-]+/i';
  $version1 = preg_split($pattern, $version1);
  $version2 = preg_split($pattern, $version2);
  $max = max(count($version1), count($version2));
  $version1 = implode('.', array_pad($version1, $max, '0'));
  $version2 = implode('.', array_pad($version2, $max, '0'));
  return version_compare($version1, $version2, $comparator);
}

// ------------------------------------- Allgemeine PHP Functions

function rex_highlight_string($string, $return = false)
{
  $s = '<p class="rex-code">'. highlight_string($string, true) .'</p>';
  if($return)
  {
    return $s;
  }
  echo $s;
}