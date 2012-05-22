<?php

/**
 * @package redaxo5
 */

/**
 * Klasse zur Formatierung von Strings
 */
abstract class rex_formatter
{
  private function __construct()
  {
    // it's not allowed to create instances of this class
  }

  /**
   * Formatiert den String <code>$value</code>
   *
   * @param $value zu formatierender String
   * @param $format_type Formatierungstype
   * @param $format Format
   *
   * Unterstützte Formatierugen:
   *
   * - <Formatierungstype>
   *    + <Format>
   *
   * - sprintf
   *    + siehe www.php.net/sprintf
   * - date
   *    + siehe www.php.net/date
   * - strftime
   *    + dateformat
   *    + datetime
   *    + siehe www.php.net/strftime
   * - number
   *    + siehe www.php.net/number_format
   *    + array( <Kommastelle>, <Dezimal Trennzeichen>, <Tausender Trennzeichen>)
   * - email
   *    + array( 'attr' => <Linkattribute>, 'params' => <Linkparameter>,
   * - url
   *    + array( 'attr' => <Linkattribute>, 'params' => <Linkparameter>,
   * - truncate
   *    + array( 'length' => <String-Laenge>, 'etc' => <ETC Zeichen>, 'break_words' => <true/false>,
   * - nl2br
   *    + siehe www.php.net/nl2br
   * - custom
   *    + formatiert den Wert anhand einer Benutzer definierten Callback Funktion
   * - bytes
   *    + formatiert einen Zahlenwert und gibt ihn als Bytegröße aus
   */
  static public function format($value, $format_type, $format)
  {
    // Stringformatierung mit sprintf()
    if ($format_type == 'sprintf')
    {
      $value = rex_formatter::_formatSprintf($value, $format);
    }
    // Datumsformatierung mit date()
    elseif ($format_type == 'date')
    {
      $value = rex_formatter::_formatDate($value, $format);
    }
    // Datumsformatierung mit strftime()
    elseif ($format_type == 'strftime')
    {
      $value = rex_formatter::_formatStrftime($value, $format);
    }
    // Zahlenformatierung mit number_format()
    elseif ($format_type == 'number')
    {
      $value = rex_formatter::_formatNumber($value, $format);
    }
    // Email-Mailto Linkformatierung
    elseif ($format_type == 'email')
    {
      $value = rex_formatter::_formatEmail($value, $format);
    }
    // URL-Formatierung
    elseif ($format_type == 'url')
    {
      $value = rex_formatter::_formatUrl($value, $format);
    }
    // String auf eine eine Länge abschneiden
    elseif ($format_type == 'truncate')
    {
      $value = rex_formatter::_formatTruncate($value, $format);
    }
    // Newlines zu <br />
    elseif ($format_type == 'nl2br')
    {
      $value = rex_formatter::_formatNl2br($value, $format);
    }
    // Benutzerdefinierte Callback-Funktion
    elseif ($format_type == 'custom')
    {
      $value = rex_formatter::_formatCustom($value, $format);
    }
    elseif ($format_type == 'bytes')
    {
      $value = rex_formatter::_formatBytes($value, $format);
    }

    return $value;
  }

  static private function _formatSprintf($value, $format)
  {
    if ($format == '')
    {
      $format = '%s';
    }
    return sprintf($format, $value);
  }

  static private function _formatDate($value, $format)
  {
    if ($format == '')
    {
      $format = 'd.m.Y';
    }

    return date($format, $value);
  }

  static private function _formatStrftime($value, $format)
  {
    if (empty ($value))
    {
      return '';
    }

    if ($format == '' || $format == 'date')
    {
      // Default REX-Dateformat
      $format = rex_i18n::msg('dateformat');
    }
    elseif ($format == 'datetime')
    {
      // Default REX-Datetimeformat
      $format = rex_i18n::msg('datetimeformat');
    }
    return strftime($format, $value);
  }

  static private function _formatNumber($value, $format)
  {
    if (!is_array($format))
    {
      $format = array ();
    }

    // Kommastellen
    if (!isset($format[0]))
    {
      $format[0] = 2;
    }
    // Dezimal Trennzeichen
    if (!isset($format[1]))
    {
      $format[1] = ',';
    }
    // Tausender Trennzeichen
    if (!isset($format[2]))
    {
      $format[2] = ' ';
    }
    return number_format($value, $format[0], $format[1], $format[2]);
  }

  static private function _formatEmail($value, $format)
  {
    if (!is_array($format))
    {
      $format = array ();
    }

    // Linkattribute
    if (empty ($format['attr']))
    {
      $format['attr'] = '';
    }
    // Linkparameter (z.b. subject=Hallo Sir)
    if (empty ($format['params']))
    {
      $format['params'] = '';
    }
    else
    {
      if (strstr($format['params'], '?') != $format['params'])
      {
        $format['params'] = '?' . $format['params'];
      }
    }
    // Url formatierung
    return '<a href="mailto:' . htmlspecialchars($value . $format['params']) . '"' . $format['attr'] . '>' . htmlspecialchars($value) . '</a>';
  }

  static private function _formatUrl($value, $format)
  {
    if (empty($value))
      return '';

    if (!is_array($format))
      $format = array ();

    // Linkattribute
    if (empty ($format['attr']))
    {
      $format['attr'] = '';
    }
    // Linkparameter (z.b. subject=Hallo Sir)
    if (empty ($format['params']))
    {
      $format['params'] = '';
    }
    else
    {
      if (strstr($format['params'], '?') != $format['params'])
      {
        $format['params'] = '?' . $format['params'];
      }
    }
    // Protokoll
    if (!preg_match('@((ht|f)tps?|telnet|redaxo)://@', $value))
    {
      $value = 'http://' . $value;
    }

    return '<a href="' . htmlspecialchars($value . $format['params']) . '"' . $format['attr'] . '>' . htmlspecialchars($value) . '</a>';
  }

  static private function _formatTruncate($value, $format)
  {
    if (!is_array($format))
      $format = array ();

    // Max-String-laenge
    if (empty ($format['length']))
      $format['length'] = 80;

    // ETC
    if (empty ($format['etc']))
      $format['etc'] = '...';

    // Break-Words?
    if (empty ($format['break_words']))
      $format['break_words'] = false;

    return self::truncate($value, $format['length'], $format['etc'], $format['break_words']);
  }

  static private function _formatNl2br($value, $format)
  {
    return nl2br($value);
  }

  static private function _formatCustom($value, $format)
  {
    if (!is_callable($format))
    {
      if (!is_callable($format[0]))
      {
        trigger_error('Unable to find callable ' . $format[0] . ' for custom format!');
      }

      $params = array();
      $params['subject'] = $value;
      if (is_array($format[1]))
      {
        $params = array_merge($format[1], $params);
      }
      else
      {
        $params['params'] = $format[1];
      }
      // $format ist in der Form
      // array(Name des Callables, Weitere Parameter)
      return call_user_func($format[0], $params);
    }

    return call_user_func($format, $value);
  }

  static private function _formatBytes($value, $format)
  {
    $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
    $unit_index = 0;
    while (($value / 1024) >= 1)
    {
      $value /= 1024;
      $unit_index++;
    }

    if (isset($format[0]))
    {
      $z = intval($value * pow(10, $precision = intval($format[0])));
      for ($i = 0; $i < intval($precision); $i++)
      {
        if (($z % 10) == 0)
        {
          $format[0] = intval($format[0]) - 1;
          $z = intval($z / 10);
        }
        else
        {
          break;
        }
      }
    }

    return rex_formatter::_formatNumber($value, $format) . ' ' . $units[$unit_index];
  }

  /**
   * Returns the truncated $string
   *
   * @param $string String Searchstring
   * @param $start String Suffix to search for
   */
  static public function truncate($string, $length = 80, $etc = '...', $break_words = false)
  {
    if ($length == 0)
      return '';

    if (strlen($string) > $length)
    {
      $length -= strlen($etc);
      if (!$break_words)
        $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length +1));

      return substr($string, 0, $length) . $etc;
    }
    else
      return $string;
  }
}
