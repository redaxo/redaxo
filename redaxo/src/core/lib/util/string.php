<?php

/**
 * String utility class
 *
 * @package redaxo\core
 */
class rex_string
{
    /**
     * Returns the string size in bytes
     *
     * @param string $string String
     * @return integer Size in bytes
     */
    public static function size($string)
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
    public static function split($string)
    {
        $string = trim($string);
        if (empty($string)) {
            return [];
        }
        $result = [];
        $spacer = '@@@REX_SPACER@@@';
        $quoted = [];

        $pattern = '@(["\'])((?:.*[^\\\\])?(?:\\\\\\\\)*)\\1@Us';
        $callback = function ($match) use ($spacer, &$quoted) {
            $quoted[] = str_replace(['\\' . $match[1], '\\\\'], [$match[1], '\\'], $match[2]);
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
     * Splits a version string
     *
     * @param string $version Version
     * @return array Version parts
     */
    public static function versionSplit($version)
    {
        return preg_split('/(?<=\d)(?=[a-z])|(?<=[a-z])(?=\d)|[ ._-]+/i', $version);
    }

    /**
     * Compares two version number strings
     *
     * In contrast to version_compare() it treats "1.0" and "1.0.0" as equal
     *
     * @link http://www.php.net/manual/en/function.version-compare.php
     *
     * @param string $version1   First version number
     * @param string $version2   Second version number
     * @param string $comparator Optional comparator
     * @return integer|boolean
     */
    public static function versionCompare($version1, $version2, $comparator = null)
    {
        $version1 = self::versionSplit($version1);
        $version2 = self::versionSplit($version2);
        $max = max(count($version1), count($version2));
        $version1 = implode('.', array_pad($version1, $max, '0'));
        $version2 = implode('.', array_pad($version2, $max, '0'));
        return version_compare($version1, $version2, $comparator);
    }

    /**
     * Returns a string containing the YAML representation of $value.
     *
     * @param array $value  The value being encoded
     * @param int   $inline The level where you switch to inline YAML
     * @return string
     */
    public static function yamlEncode(array $value, $inline = 3)
    {
        return Symfony\Component\Yaml\Yaml::dump($value, $inline, 4);
    }

    /**
     * Parses YAML into a PHP array.
     *
     * @param string $value YAML string
     * @return array
     */
    public static function yamlDecode($value)
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
    public static function buildQuery(array $params, $argSeparator = '&')
    {
        $query = [];
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
     * Returns a string by key="value" pair
     *
     * @param array $attributes
     * @return string
     */
    public static function buildAttributes(array $attributes)
    {
        $attr = '';

        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $attr .= ' ' . $key . '="' . $value . '"';
            }
        }

        return $attr;
    }

    /**
     * Highlights a string
     *
     * @param string $string
     * @return string
     */
    public static function highlight($string)
    {
        return '<p class="rex-code">' . highlight_string($string, true) . '</p>';
    }
}
