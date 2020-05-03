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
        return Normalizer::normalize($string, Normalizer::FORM_C);
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

        $pattern = '@(?<=\s|=|^)(["\'])((?:.*[^\\\\])?(?:\\\\\\\\)*)\\1(?=\s|$)@Us';
        $callback = static function ($match) use ($spacer, &$quoted) {
            $quoted[] = str_replace(['\\' . $match[1], '\\\\'], [$match[1], '\\'], $match[2]);
            return $spacer;
        };
        $string = preg_replace_callback($pattern, $callback, $string);

        $parts = preg_split('@\s+@', $string);
        $i = 0;
        foreach ($parts as $part) {
            $part = explode('=', $part, 2);
            if (isset($part[1])) {
                /** @psalm-suppress EmptyArrayAccess */
                $value = $part[1] == $spacer ? $quoted[$i++] : $part[1];
                $result[$part[0]] = $value;
            } else {
                /** @psalm-suppress EmptyArrayAccess */
                $value = $part[0] == $spacer ? $quoted[$i++] : $part[0];
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * @deprecated since 5.10, use `rex_version::split` instead
     */
    public static function versionSplit($version)
    {
        return rex_version::split($version);
    }

    /**
     * @deprecated since 5.10, use `rex_version::compare` instead
     */
    public static function versionCompare($version1, $version2, $comparator = null)
    {
        return rex_version::compare($version1, $version2, $comparator);
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
     * @throws rex_yaml_parse_exception
     *
     * @return array
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
     * @param string $argSeparator
     *
     * @return string
     */
    public static function buildQuery(array $params, $argSeparator = '&')
    {
        $query = [];
        $func = static function (array $params, $fullkey = null) use (&$query, &$func) {
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
     * @return string
     */
    public static function buildAttributes(array $attributes)
    {
        $attr = '';

        foreach ($attributes as $key => $value) {
            if (is_int($key)) {
                $attr .= ' ' . rex_escape($value);
            } else {
                if (is_array($value)) {
                    $value = implode(' ', $value);
                }
                // for bc reasons avoid double escaping of "&", especially in already escaped urls
                $value = str_replace('&amp;', '&', $value);
                $attr .= ' ' . rex_escape($key) . '="' . rex_escape($value) . '"';
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

    /**
     * Cleanup the given html string and removes possible malicious codes/markup.
     */
    public static function sanitizeHtml(string $html): string
    {
        static $antiXss;

        if (!$antiXss) {
            $antiXss = new voku\helper\AntiXSS();
            $antiXss->removeEvilAttributes(['style']);
            $antiXss->removeNeverAllowedStrAfterwards(['&lt;script&gt;', '&lt;/script&gt;']);
        }

        return $antiXss->xss_clean($html);
    }
}
