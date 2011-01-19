<?php

/**
 * Funktionen zur Ausgabe der Titel Leiste und Subnavigation
 * @package redaxo4
 * @version svn:$Id$
 */

/**
 * Berechnet aus einem Relativen Pfad einen Absoluten
 */
function rex_absPath($rel_path, $rel_to_current = false)
{

  $stack = array();
  // Pfad relativ zum aktuellen Verzeichnis?
  // z.b. ../../files
  if($rel_to_current)
  {
    $path = realpath('.');
    $stack = explode(DIRECTORY_SEPARATOR, $path);
  }

  // pfadtrenner vereinheitlichen
  $rel_path = str_replace('\\', '/', $rel_path);
  foreach (explode('/', $rel_path) as $dir)
  {
    // Aktuelles Verzeichnis, oder Ordner ohne Namen
    if ($dir == '.' || $dir == '')
      continue;

    // Zum Parent
    if ($dir == '..')
      array_pop($stack);
    // Normaler Ordner
    else
      array_push($stack, $dir);
  }

  return implode(DIRECTORY_SEPARATOR, $stack);
}

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
  global $REX;

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

    $state = $REX['I18N']->msg($key, '<span class="rex-error">', '</span>', rex_absPath($file));
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

function rex_getAttributes($name,$content,$default = null)
{
  $prop = unserialize($content);
  if (isset($prop[$name])) return $prop[$name];
  return $default;
}

function rex_setAttributes($name,$value,$content)
{
  $prop = unserialize($content);
  $prop[$name] = $value;
  return serialize($prop);
}

/**
 * Gibt den nächsten freien Tabindex zurück.
 * Der Tabindex ist eine stetig fortlaufende Zahl,
 * welche die Priorität der Tabulatorsprünge des Browsers regelt.
 *
 * @return integer nächster freier Tabindex
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


function array_insert($array, $index, $value)
{
  // In PHP5 akzeptiert array_merge nur arrays. Deshalb hier $value als Array verpacken
  return array_merge(array_slice($array, 0, $index), array($value), array_slice($array, $index));
}

function rex_message($message, $cssClass, $sorround_tag)
{
  $return = '';

  $return = '<div class="rex-message"><'. $sorround_tag .' class="'. $cssClass .'">';

  if ($sorround_tag != 'p')
    $return .= '<p>';

  $return .= '<span>'. $message .'</span>';

  if ($sorround_tag != 'p')
    $return .= '</p>';

  $return .= '</'. $sorround_tag .'></div>';

  return $return;
}

function rex_info($message, $cssClass = null, $sorround_tag = null)
{
  if(!$cssClass) $cssClass = 'rex-info';
  if(!$sorround_tag) $sorround_tag = 'div';
  return rex_message($message, $cssClass, $sorround_tag);
}

function rex_warning($message, $cssClass = null, $sorround_tag = null)
{
  if(!$cssClass) $cssClass = 'rex-warning';
  if(!$sorround_tag) $sorround_tag = 'div';
  return rex_message($message, $cssClass, $sorround_tag);
}

function rex_info_block($message, $cssClass = null, $sorround_tag = null)
{
  if(!$cssClass) $cssClass = 'rex-info-block';
  if(!$sorround_tag) $sorround_tag = 'div';
  return rex_message_block($message, $cssClass, $sorround_tag);
}

function rex_warning_block($message, $cssClass = null, $sorround_tag = null)
{
  if(!$cssClass) $cssClass = 'rex-warning-block';
  if(!$sorround_tag) $sorround_tag = 'div';
  return rex_message_block($message, $cssClass, $sorround_tag);
}

function rex_message_block($message, $cssClass, $sorround_tag)
{
  return '<div class="rex-message-block">
            <'. $sorround_tag .' class="'. $cssClass .'">
              <div class="rex-message-content">
                '. $message .'
              </div>
            </'. $sorround_tag .'>
          </div>';
}

function rex_content_block($content)
{
  return '<div class="rex-content-block"><div class="rex-content-block-content">'. $content .'</div></div>';
}

function rex_accesskey($title, $key)
{
  global $REX;

  if($REX['USER']->hasPerm('accesskeys[]'))
    return ' accesskey="'. $key .'" title="'. $title .' ['. $key .']"';

  return ' title="'. $title .'"';
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
 * Uebersetzt den text $text, falls dieser mit dem prefix "translate:" beginnt.
 * Ansonsten wird $text zurueckgegeben.
 *
 * @param string $text The text for translation.
 * @param i18n $I18N_Catalogue The catalogue for translation. If null use the system-catalogue by default
 * @param boolean $use_htmlspecialchars Flag whether the translated text should be passed to htmlspecialchars()
 */
function rex_translate($text, $I18N_Catalogue = null, $use_htmlspecialchars = true)
{
  if(!is_string($text))
  {
    throw new InvalidArgumentException('Expecting $text to be a String, "'. gettype($text) .'" given!');
  }

  if(!$I18N_Catalogue)
  {
    global $REX;

    if(!isset($REX['I18N']) || !is_object($REX['I18N']))
      $REX['I18N'] = rex_create_lang($REX['LANG']);

    if(!$REX['I18N'])
      trigger_error('Unable to create language "'. $REX['LANG'] .'"', E_USER_ERROR);

    return rex_translate($text, $REX['I18N'], $use_htmlspecialchars);
  }

  $tranKey = 'translate:';
  $transKeyLen = strlen($tranKey);
  if(substr($text, 0, $transKeyLen) == $tranKey)
  {
    $text = $I18N_Catalogue->msg(substr($text, $transKeyLen));
  }

  if($use_htmlspecialchars)
    return htmlspecialchars($text);

  return $text;
}

/**
 * Uebersetzt alle texte in $array die mit "translate:" beginnen.
 *
 * @param array $text The Array of Strings for translation.
 * @param i18n $I18N_Catalogue The catalogue for translation. If null use the system-catalogue by default
 * @param boolean $use_htmlspecialchars Flag whether the translated text should be passed to htmlspecialchars()
 */
function rex_translate_array($array, $I18N_Catalogue = null, $use_htmlspecialchars = true)
{
  if(is_array($array))
  {
    foreach($array as $key => $value)
    {
      $array[$key] = rex_translate_array($value, $I18N_Catalogue, $use_htmlspecialchars);
    }
    return $array;
  }
  else if (is_string($array))
  {
    return rex_translate($array, $I18N_Catalogue, $use_htmlspecialchars);
  }
  else if (is_scalar($array))
  {
    return $array;
  }
  else
  {
    throw new InvalidArgumentException('Expecting $text to be a String or Array of Strings, "'. gettype($array) .'" given!');
  }
}

/**
 * Leitet auf einen anderen Artikel weiter
 */
function rex_redirect($article_id, $clang = '', $params = array())
{
  global $REX;

  // Alle OBs schließen
  while(@ob_end_clean());

  $divider = '&';

  header('Location: '. rex_getUrl($article_id, $clang, $params, $divider));
  exit();
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

function rex_put_file_contents($path, $content)
{
  global $REX;

  $writtenBytes = file_put_contents($path, $content);
  @ chmod($path, $REX['FILEPERM']);

  return $writtenBytes;
}

function rex_get_file_contents($path)
{
  return file_get_contents($path);
}

function rex_replace_dynamic_contents($path, $content)
{
  if($fcontent = rex_get_file_contents($path))
  {
    $content = "// --- DYN\n". trim($content) ."\n// --- /DYN";
    $fcontent = preg_replace("@(\/\/.---.DYN.*\/\/.---.\/DYN)@s", $content, $fcontent);
    return rex_put_file_contents($path, $fcontent);
  }
  return false;
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
 */
function rex_organize_priorities($tableName, $priorColumnName, $whereCondition = '', $orderBy = '', $id_field='id')
{
//  // Datenbankvariable initialisieren
//  $qry = 'SET @count='. ($startBy - 1);
//  $sql = rex_sql::getInstance();
//  $sql->setQuery($qry);
//
//  // Spalte updaten
//  $qry = 'UPDATE '. $tableName .' SET '. $priorColumnName .' = ( SELECT @count := @count +1 )';
//
//  if($whereCondition != '')
//    $qry .= ' WHERE '. $whereCondition;
//
//  if($orderBy != '')
//    $qry .= ' ORDER BY '. $orderBy;
//
//  $sql = rex_sql::getInstance();
//  $sql->setQuery($qry);

  $qry = 'select * from '.$tableName;
  if($whereCondition != '')
    $qry .= ' WHERE '. $whereCondition;
  if($orderBy != '')
    $qry .= ' ORDER BY '. $orderBy;

  $gu = rex_sql::factory();
  $gr = rex_sql::factory();
  $gr->setQuery($qry);
  for ($i = 0; $i < $gr->getRows(); $i ++)
  {
      $gu->setQuery('update '.$tableName.' set '.$priorColumnName.'='.($i+1).' where '.$id_field.'='.$gr->getValue($id_field));
      $gr->next();
  }
}

function rex_version_compare($version1, $version2, $comparator = null)
{
  $version1 = preg_replace('/(\.0)*$/', '', $version1);
  $version2 = preg_replace('/(\.0)*$/', '', $version2);
  return version_compare($version1, $version2, $comparator);
}

/**
 * Escaped einen String
 *
 * @param $string Zu escapender String
 */
function rex_addslashes($string, $flag = '\\\'\"')
{
  if ($flag == '\\\'\"')
  {
    $string = str_replace('\\', '\\\\', $string);
    $string = str_replace('\'', '\\\'', $string);
    $string = str_replace('"', '\"', $string);
  }elseif ($flag == '\\\'')
  {
    $string = str_replace('\\', '\\\\', $string);
    $string = str_replace('\'', '\\\'', $string);
  }
  return $string;
}

// ------------------------------------- Allgemeine PHP Functions

/* PHP5 Functions */

if (!function_exists("htmlspecialchars_decode"))
{
  function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT) {
    return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
  }
}

if (!function_exists('file_put_contents'))
{
  function file_put_contents($path, $content)
  {
    $fp = @fopen($path, 'wb');
    if ($fp)
    {
      $writtenBytes = fwrite($fp, $content, strlen($content));

      if(fclose($fp))
        return $writtenBytes;
    }
    return false;
  }
}

function rex_highlight_string($string, $return = false)
{
  $s = '<p class="rex-code">'. highlight_string($string, true) .'</p>';
  if($return)
  {
    return $s;
  }
  echo $s;
}

function rex_highlight_file($filename, $return = false)
{
  $s = '<p class="rex-code">'. highlight_file($filename, true) .'</p>';
  if($return)
  {
    return $s;
  }
  echo $s;
}

// make objectcloning work for php4
// see http://acko.net/node/54
// usage: $cloned = clone($yourObject);
if (version_compare(phpversion(), '5.0') < 0 && !function_exists('clone')) {
  eval('
  function clone($object) {
    return $object;
  }
  ');
}

/**
 * Funktion zum Anlegen eines Sprache-Objekts
 *
 * @param $locale Locale der Sprache
 * @param $searchpath Pfad zum Ordner indem die Sprachdatei gesucht werden soll
 * @param $setlocale TRUE, wenn die locale für die Umgebung gesetzt werden soll, sonst FALSE
 * @return unknown_type
 */
function rex_create_lang($locale = "de_de", $searchpath = '', $setlocale = TRUE)
{
  global $REX;

  $_searchpath = $searchpath;

  if ($searchpath == '')
  {
    $searchpath = $REX['INCLUDE_PATH'] .DIRECTORY_SEPARATOR. "/core/lang";
  }
  $lang_object = new rex_i18n($locale, $searchpath);

  if ($_searchpath == '')
  {
    $REX['LOCALES'] = $lang_object->getLocales($searchpath);
  }

  if($setlocale)
  {
    $locales = array();
    foreach(explode(',', trim($lang_object->msg('setlocale'))) as $locale)
    {
      $locales[]= $locale .'.UTF-8';
      $locales[]= $locale .'.UTF8';
      $locales[]= $locale .'.utf-8';
      $locales[]= $locale .'.utf8';
    }

    foreach(explode(',', trim($lang_object->msg('setlocale'))) as $locale)
      $locales[]= $locale;

    setlocale(LC_ALL, $locales);
  }

  return $lang_object;
}

/**
 * Prueft, ob der aktuelle Benutzer im Backend eingeloggt ist.
 *
 * Diese Funktion kann auch aus dem Frontend heraus verwendet werden.
 */
function rex_hasBackendSession()
{
  global $REX;

  if(!isset($_SESSION))
    return false;

  if(!isset($REX))
    return false;

  if(!isset($REX['INSTNAME']))
    return false;

  if(!isset($_SESSION[$REX['INSTNAME']]))
    return false;

  return $_SESSION[$REX['INSTNAME']]['UID'] > 0;
}