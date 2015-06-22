<?php

/**
 * String formatter class.
 *
 * @package redaxo\core
 */
abstract class rex_formatter
{
    /**
     * It's not allowed to create instances of this class.
     */
    private function __construct()
    {
    }

    /**
     * Formats a string by the given format type.
     *
     * @param string $value      Value
     * @param string $formatType Format type (any method name of this class)
     * @param mixed  $format     For possible values look at the other methods of this class
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public static function format($value, $formatType, $format)
    {
        if (!is_callable([__CLASS__, $formatType])) {
            throw new InvalidArgumentException('Unknown $formatType: "' . $formatType . '"!');
        }
        return self::$formatType($value, $format);
    }

    /**
     * Formats a string by `date()`.
     *
     * @link http://www.php.net/manual/en/function.date.php
     *
     * @param string $value  Value
     * @param string $format Default format is `d.m.Y`
     *
     * @return string
     */
    public static function date($value, $format = '')
    {
        if ($format == '') {
            $format = 'd.m.Y';
        }

        return date($format, $value);
    }

    /**
     * Formats a string by `strftime()`.
     *
     * @link http://www.php.net/manual/en/function.strftime.php
     *
     * @param string $value  Value
     * @param string $format Possible values are format strings like in `strftime` or "date" oder "datetime", default is "date"
     *
     * @return string
     */
    public static function strftime($value, $format = '')
    {
        if (empty($value)) {
            return '';
        }

        if ($format == '' || $format == 'date') {
            // Default REX-Dateformat
            $format = rex_i18n::msg('dateformat');
        } elseif ($format == 'datetime') {
            // Default REX-Datetimeformat
            $format = rex_i18n::msg('datetimeformat');
        }
        return strftime($format, $value);
    }

    /**
     * Formats a string by `number_format()`.
     *
     * @link http://www.php.net/manual/en/function.number-format.php
     *
     * @param string $value  Value
     * @param array  $format Array with number of decimals, decimals point and thousands separator, default is `array(2, ',', ' ')`
     *
     * @return string
     */
    public static function number($value, $format = [])
    {
        if (!is_array($format)) {
            $format = [];
        }

        // Kommastellen
        if (!isset($format[0])) {
            $format[0] = 2;
        }
        // Dezimal Trennzeichen
        if (!isset($format[1])) {
            $format[1] = ',';
        }
        // Tausender Trennzeichen
        if (!isset($format[2])) {
            $format[2] = ' ';
        }
        return number_format($value, $format[0], $format[1], $format[2]);
    }

    /**
     * Formats a string as bytes.
     *
     * @param string $value  Value
     * @param array  $format Same as {@link rex_formatter::number()}
     *
     * @return string
     */
    public static function bytes($value, $format = [])
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        $unit_index = 0;
        while (($value / 1024) >= 1) {
            $value /= 1024;
            ++$unit_index;
        }

        if (isset($format[0])) {
            $z = intval($value * pow(10, $precision = intval($format[0])));
            for ($i = 0; $i < intval($precision); ++$i) {
                if (($z % 10) == 0) {
                    $format[0] = intval($format[0]) - 1;
                    $z = intval($z / 10);
                } else {
                    break;
                }
            }
        }

        return self::number($value, $format) . ' ' . $units[$unit_index];
    }

    /**
     * Formats a string by `sprintf()`.
     *
     * @link http://www.php.net/manual/en/function.sprintf.php
     *
     * @param string $value  Value
     * @param string $format
     *
     * @return string
     */
    public static function sprintf($value, $format = '')
    {
        if ($format == '') {
            $format = '%s';
        }
        return sprintf($format, $value);
    }

    /**
     * Formats a string by `nl2br`.
     *
     * @link http://www.php.net/manual/en/function.nl2br.php
     *
     * @param string $value Value
     *
     * @return string
     */
    public static function nl2br($value)
    {
        return nl2br($value);
    }

    /**
     * Truncates a string.
     *
     * @param string $value  Value
     * @param array  $format Default format is `array('length' => 80, 'etc' => '...', 'break_words' => false)`
     *
     * @return string
     */
    public static function truncate($value, $format = [])
    {
        if (!is_array($format)) {
            $format = [];
        }

        // Max-String-laenge
        if (empty($format['length'])) {
            $format['length'] = 80;
        }

        // ETC
        if (empty($format['etc'])) {
            $format['etc'] = '...';
        }

        // Break-Words?
        if (empty($format['break_words'])) {
            $format['break_words'] = false;
        }

        if (mb_strlen($value) > $format['length']) {
            $format['length'] -= mb_strlen($format['etc']);
            if (!$format['break_words']) {
                $value = preg_replace('/\s+?(\S+)?$/', '', substr($value, 0, $format['length'] + 1));
            }

            return substr($value, 0, $format['length']) . $format['etc'];
        }

        return $value;
    }

    /**
     * Avoid widows in a string.
     *
     * @param string $value
     *
     * @return string
     */
    public static function widont($value)
    {
        // Sollte ein Wort allein auf einer Zeile vorkommen, wird dies unterbunden
        $value = rtrim($value);
        $space = strrpos($value, ' ');
        if ($space !== false) {
            $value = substr($value, 0, $space) . '&#160;' . substr($value, $space + 1);
        }
        return $value;
    }

    /**
     * Formats a version string by `sprintf()`.
     *
     * @link http://www.php.net/manual/en/function.sprintf.php
     *
     * @param string $value  Version
     * @param string $format Version format, e.g. "%s.%s"
     *
     * @return string
     */
    public static function version($value, $format)
    {
        return vsprintf($format, rex_string::versionSplit($value));
    }

    /**
     * Formats a string as link.
     *
     * @param string $value  URL
     * @param array  $format Array with link attributes and params
     *
     * @return string Link
     */
    public static function url($value, $format = [])
    {
        if (empty($value)) {
            return '';
        }

        if (!is_array($format)) {
            $format = [];
        }

        // Linkattribute
        if (empty($format['attr'])) {
            $format['attr'] = '';
        }
        // Linkparameter (z.b. subject=Hallo Sir)
        if (empty($format['params'])) {
            $format['params'] = '';
        } else {
            if (strstr($format['params'], '?') != $format['params']) {
                $format['params'] = '?' . $format['params'];
            }
        }
        // Protokoll
        if (!preg_match('@((ht|f)tps?|telnet|redaxo)://@', $value)) {
            $value = 'http://' . $value;
        }

        return '<a href="' . htmlspecialchars($value . $format['params']) . '"' . $format['attr'] . '>' . htmlspecialchars($value) . '</a>';
    }

    /**
     * Formats a string as email link.
     *
     * @param string $value  Email
     * @param array  $format Array with link attributes and params
     *
     * @return string Email link
     */
    public static function email($value, $format = [])
    {
        if (!is_array($format)) {
            $format = [];
        }

        // Linkattribute
        if (empty($format['attr'])) {
            $format['attr'] = '';
        }
        // Linkparameter (z.b. subject=Hallo Sir)
        if (empty($format['params'])) {
            $format['params'] = '';
        } else {
            if (strstr($format['params'], '?') != $format['params']) {
                $format['params'] = '?' . $format['params'];
            }
        }
        // Url formatierung
        return '<a href="mailto:' . htmlspecialchars($value . $format['params']) . '"' . $format['attr'] . '>' . htmlspecialchars($value) . '</a>';
    }

    /**
     * Formats a string by a custom callable.
     *
     * @param string         $value  Value
     * @param callable|array $format A callable or an array of a callable and additional params
     *
     * @return string
     *
     * @throws rex_exception
     */
    public static function custom($value, $format)
    {
        if (!is_callable($format)) {
            if (!is_callable($format[0])) {
                throw new rex_exception('Unable to find callable ' . $format[0] . ' for custom format!');
            }

            $params = [];
            $params['subject'] = $value;
            if (is_array($format[1])) {
                $params = array_merge($format[1], $params);
            } else {
                $params['params'] = $format[1];
            }
            // $format ist in der Form
            // array(Name des Callables, Weitere Parameter)
            return call_user_func($format[0], $params);
        }

        return call_user_func($format, $value);
    }
}
