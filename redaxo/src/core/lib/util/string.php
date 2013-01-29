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
    $string = trim($string);
    if (empty($string)) {
      return array();
    }
    $result = array();
    $spacer = '@@@REX_SPACER@@@';
    $quoted = array();

    $pattern = '@(["\'])((?:.*[^\\\\])?(?:\\\\\\\\)*)\\1@Us';
    $callback = function ($match) use ($spacer, &$quoted) {
      $quoted[] = str_replace(array('\\' . $match[1], '\\\\'), array($match[1], '\\'), $match[2]);
      return $spacer;
    };
    $string = preg_replace_callback($pattern, $callback, $string);

    $parts = preg_split('@\s+@', $string);
    $i = 0;
    foreach ($parts as $part) {
      $part = explode('=', $part, 2);
      if (isset($part[1])) {
        $value = $part[1] == $spacer ? $quoted[$i++] : $part[1];
        $result[$part[0]] = $value;
      } else {
        $value = $part[0] == $spacer ? $quoted[$i++] : $part[0];
        $result[] = $value;
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
   * @param string $version1   First version number
   * @param string $version1   Second version number
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
   * Returns a string containing the YAML representation of $value.
   *
   * @param array  $value  The value being encoded
   * @param number $inline The level where you switch to inline YAML
   * @return string
   */
  static public function yamlEncode(array $value, $inline = 3)
  {
    return Symfony\Component\Yaml\Yaml::dump($value, $inline, 2);
  }

  /**
   * Parses YAML into a PHP array.
   *
   * @param string $value YAML string
   * @return array
   */
  static public function yamlDecode($value)
  {
    return Symfony\Component\Yaml\Yaml::parse($value);
  }

  /**
   * Generates URL-encoded query string
   *
   * @param array  $params
   * @param string $argSeparator
   * @return string
   */
  static public function buildQuery(array $params, $argSeparator = '&')
  {
    $query = array();
    $func = function (array $params, $fullkey = null) use (&$query, &$func) {
      foreach ($params as $key => $value) {
        $key = $fullkey ? $fullkey . '[' . urlencode($key) . ']' : urlencode($key);
        if (is_array($value)) {
          $func($value, $key);
        } else {
          $query[] = $key . '=' . str_replace('%2F', '/', urlencode($value));
        }
      }
    };
    $func($params);
    return implode($argSeparator, $query);
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


  /**
   * Avoid widows in a string
   *
   * @param string $string
   * @return string
   */
  static public function widont($string)
  {
    // Sollte ein Wort allein auf einer Zeile vorkommen, wird dies unterbunden
    $string = rtrim($string);
    $space = strrpos($string, ' ');
    if ($space !== false) {
      $string = substr($string, 0, $space) . '&#160;' . substr($string, $space + 1);
    }
    return $string;
  }
}
