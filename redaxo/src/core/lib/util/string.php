<?php

/**
 * String utility class
 *
 * @package redaxo5
 */
class rex_string
{
  /**
   * Returns the string size in bytes
   *
   * @param string $string String
   * @return integer Size in bytes
   */
  static public function size($string)
  {
    return mb_strlen($string, '8bit');
  }

  /**
   * Splits a string by spaces
   * (Strings with quotes will be regarded)
   *
   * Examples:
   * "a b 'c d'"   -> array('a', 'b', 'c d')
   * "a=1 b='c d'" -> array('a' => 1, 'b' => 'c d')
   *
   * @param string $string
   * @return array
   */
  static public function split($string)
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
    if (strpos($string, '=') === false) {
      $parts = explode(' ', $string);
      foreach ($parts as $part) {
        if (empty ($part))
          continue;

        if ($part == $spacer) {
          $result[] = array_shift($quoted);
        } else {
          $result[] = $part;
        }
      }
    }
    // ------------ z.b. a=4 b="av c" y='de f' z=ghi
    else {
      $parts = explode(' ', $string);
      foreach ($parts as $part) {
        if (empty($part))
          continue;

        $variable = explode('=', $part);

        if (empty ($variable[0]) || empty ($variable[1]))
          continue;

        $var_name = $variable[0];
        $var_value = $variable[1];

        if ($var_value == $spacer) {
          $var_value = array_shift($quoted);
        }

        $result[$var_name] = $var_value;
      }
    }
    return $result;
  }

  /**
   * Compares two version number strings
   *
   * In contrast to version_compare() it treats "1.0" and "1.0.0" as equal
   *
   * @link http://www.php.net/manual/en/function.version-compare.php
   *
   * @param string $version1 First version number
   * @param string $version1 Second version number
   * @param string $comparator Optional comparator
   * @return integer|boolean
   */
  static public function compareVersions($version1, $version2, $comparator = null)
  {
    $pattern = '/(?<=\d)(?=[a-z])|(?<=[a-z])(?=\d)|[ .-]+/i';
    $version1 = preg_split($pattern, $version1);
    $version2 = preg_split($pattern, $version2);
    $max = max(count($version1), count($version2));
    $version1 = implode('.', array_pad($version1, $max, '0'));
    $version2 = implode('.', array_pad($version2, $max, '0'));
    return version_compare($version1, $version2, $comparator);
  }

  /**
   * Highlights a string
   *
   * @param string $string
   * @return string
   */
  static public function highlight($string)
  {
    return '<p class="rex-code">' . highlight_string($string, true) . '</p>';
  }
}
