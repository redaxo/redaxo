<?php

/**
 * String utility class.
 *
 * @package redaxo\core
 */
class rex_string
{
    /**
     * Returns the string size in bytes.
     *
     * @param string $string String
     *
     * @return int Size in bytes
     */
    public static function size($string)
    {
        return mb_strlen($string, '8bit');
    }

    /**
     * Normalizes the encoding of a string (UTF8 NFD to NFC).
     *
     * On HFS+ filesystem (OS X) filenames are stored in UTF8 NFD while all other filesystems are
     * using UTF8 NFC. NFC is more common in general.
     *
     * @param string $string Input string
     *
     * @return string
     */
    public static function normalizeEncoding($string)
    {
        static $normalizer;

        if (null === $normalizer) {
            if (function_exists('normalizer_normalize')) {
                $normalizer = function ($string) {
                    return normalizer_normalize($string, Normalizer::FORM_C);
                };
            } else {
                $normalizer = function ($string) {
                    return str_replace(
                        ["A\xcc\x88", "a\xcc\x88", "O\xcc\x88", "o\xcc\x88", "U\xcc\x88", "u\xcc\x88"],
                        ['Ä', 'ä', 'Ö', 'ö', 'Ü', 'ü'],
                        $string
                    );
                };
            }
        }

        return $normalizer($string);
    }

    /**
     * Normalizes a string.
     *
     * Makes the string lowercase, replaces umlauts by their ascii representation (ä -> ae etc.), and replaces all
     * other chars that do not match a-z, 0-9 or $allowedChars by $replaceChar.
     *
     * @param string $string       Input string
     * @param string $replaceChar  Character that is used to replace not allowed chars
     * @param string $allowedChars Character whitelist
     *
     * @return string
     */
    public static function normalize($string, $replaceChar = '_', $allowedChars = '')
    {
        $string = self::normalizeEncoding($string);
        $string = mb_strtolower($string);
        $string = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $string);
        $string = preg_replace('/[^a-z\d' . preg_quote($allowedChars, '/') . ']+/ui', $replaceChar, $string);
        return trim($string, $replaceChar);
    }

    /**
     * Splits a string by spaces
     * (Strings with quotes will be regarded).
     *
     * Examples:
     * "a b 'c d'"   -> array('a', 'b', 'c d')
     * "a=1 b='c d'" -> array('a' => 1, 'b' => 'c d')
     *
     * @param string $string
     *
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
     * Splits a version string.
     *
     * @param string $version Version
     *
     * @return array Version parts
     */
    public static function versionSplit($version)
    {
        return preg_split('/(?<=\d)(?=[a-z])|(?<=[a-z])(?=\d)|[ ._-]+/i', $version);
    }

    /**
     * Compares two version number strings.
     *
     * In contrast to version_compare() it treats "1.0" and "1.0.0" as equal and it supports a space as separator for
     * the version parts, e.g. "1.0 beta1"
     *
     * @see http://www.php.net/manual/en/function.version-compare.php
     *
     * @param string $version1   First version number
     * @param string $version2   Second version number
     * @param string $comparator Optional comparator
     *
     * @return int|bool
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
     *
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
     *
     * @return array
     *
     * @throws rex_yaml_parse_exception
     */
    public static function yamlDecode($value)
    {
        try {
            return Symfony\Component\Yaml\Yaml::parse($value);
        } catch (Symfony\Component\Yaml\Exception\ParseException $exception) {
            throw new rex_yaml_parse_exception($exception->getMessage(), $exception);
        }
    }

    /**
     * Generates URL-encoded query string.
     *
     * @param array  $params
     * @param string $argSeparator
     *
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
     * Returns a string by key="value" pair.
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function buildAttributes(array $attributes)
    {
        $attr = '';

        foreach ($attributes as $key => $value) {
            if (is_int($key)) {
                $attr .= ' ' . $value;
            } else {
                if (is_array($value)) {
                    $value = implode(' ', $value);
                }
                $attr .= ' ' . $key . '="' . $value . '"';
            }
        }

        return $attr;
    }

    /**
     * Highlights a string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function highlight($string)
    {
        $return = str_replace(["\r", "\n"], ['', ''], highlight_string($string, true));
        return '<pre class="rex-code">' . $return . '</pre>';
    }
}
