<?php

/**
 * @package redaxo4
 * @version svn:$Id$
 */

/**
 * Klasse zur Formatierung von Strings
 */
class rex_formatter
{
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
   * - rexmedia
   *    + formatiert ein Medium via OOMedia
   * - custom
   *    + formatiert den Wert anhand einer Benutzer definierten Callback Funktion
   */
  function format($value, $format_type, $format)
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
    // REDAXO Medienpool files darstellen
    elseif ($format_type == 'rexmedia' && $value != '')
    {
      $value = rex_formatter::_formatRexMedia($value, $format);
    }
    // Artikel Id-Clang Id mit rex_getUrl() darstellen
    elseif ($format_type == 'rexurl' && $value != '')
    {
      $value = rex_formatter::_formatRexUrl($value, $format);
    }
    // Benutzerdefinierte Callback-Funktion
    elseif ($format_type == 'custom')
    {
      $value = rex_formatter::_formatCustom($value, $format);
    }

    return $value;
  }

  function _formatSprintf($value, $format)
  {
    if ($format == '')
    {
      $format = '%s';
    }
    return sprintf($format, $value);
  }

  function _formatDate($value, $format)
  {
    if ($format == '')
    {
      $format = 'd.m.Y';
    }

    return date($format, $value);
  }

  function _formatStrftime($value, $format)
  {
    global $I18N;

    if (!is_object($I18N)) $I18N = rex_create_lang();

    if (empty ($value))
    {
      return '';
    }

    if ($format == '' || $format == 'date')
    {
      // Default REX-Dateformat
      $format = $I18N->msg('dateformat');
    }
    elseif ($format == 'datetime')
    {
      // Default REX-Datetimeformat
      $format = $I18N->msg('datetimeformat');
    }
    return strftime($format, $value);
  }

  function _formatNumber($value, $format)
  {
    if (!is_array($format))
    {
      $format = array ();
    }

    // Kommastellen
    if (empty ($format[0]))
    {
      $format[0] = 2;
    }
    // Dezimal Trennzeichen
    if (empty ($format[1]))
    {
      $format[1] = ',';
    }
    // Tausender Trennzeichen
    if (empty ($format[2]))
    {
      $format[2] = ' ';
    }
    return number_format($value, $format[0], $format[1], $format[2]);
  }

  function _formatEmail($value, $format)
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
      if (!startsWith($format['params'], '?'))
      {
        $format['params'] = '?'.$format['params'];
      }
    }
    // Url formatierung
    return '<a href="mailto:'.$value.$format['params'].'"'.$format['attr'].'>'.$value.'</a>';
  }

  function _formatUrl($value, $format)
  {
    if(empty($value))
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
      if (!startsWith($format['params'], '?'))
      {
        $format['params'] = '?'.$format['params'];
      }
    }
    // Protokoll
    if (!preg_match('@((ht|f)tps?|telnet|redaxo)://@', $value))
    {
      $value = 'http://'.$value;
    }

    return '<a href="'.$value.$format['params'].'"'.$format['attr'].'>'.$value.'</a>';
  }

  function _formatTruncate($value, $format)
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

    return truncate($value, $format['length'], $format['etc'], $format['break_words']);
  }

  function _formatNl2br($value, $format)
  {
    return nl2br($value);
  }

  function _formatCustom($value, $format)
  {
    if(!is_callable($format))
    {
      if(!is_callable($format[0]))
      {
        trigger_error('Unable to find callable '. $format[0] .' for custom format!');
      }

      $params = array();
      $params['subject'] = $value;
      if(is_array($format[1]))
      {
        $params = array_merge($format[1], $params);
      }
      else
      {
        $params['params'] = $format[1];
      }
      // $format ist in der Form
      // array(Name des Callables, Weitere Parameter)
      return rex_call_func($format[0], $params);
    }

    return rex_call_func($format, $value);
  }

  function _formatRexMedia($value, $format)
  {
    if (!is_array($format))
    {
      $format = array ();
    }

    $params = $format['params'];

    // Resize aktivieren, falls nicht anders übergeben
    if (empty ($params['resize']))
    {
      $params['resize'] = true;
    }

    $media = OOMedia :: getMediaByName($value);
    // Bilder als Thumbnail
    if ($media->isImage())
    {
      $value = $media->toImage($params);
    }
    // Sonstige mit Mime-Icons
    else
    {
      $value = $media->toIcon();
    }

    return $value;
  }

  function _formatRexUrl($value, $format)
  {
    if(empty($value))
      return '';

    if (!is_array($format))
      $format = array ();

    // format in dem die werte gespeichert sind
    if (empty ($format['format']))
    {
      // default: <article-id>-<clang-id>
      $format['format'] = '%i-%i';
    }

    $hits = sscanf($value, $format['format'], $value, $format['clang']);
    if($hits == 1)
    {
      // clang
      if (empty ($format['clang']))
      {
        $format['clang'] = '';
      }
    }

    // Linkparameter (z.b. subject=Hallo Sir)
    if (empty ($format['params']))
    {
      $format['params'] = '';
    }
    else
    {
      if (!startsWith($format['params'], '?'))
      {
        $format['params'] = '?'.$format['params'];
      }
    }

    // divider
    if (empty ($format['divider']))
    {
      $format['divider'] = '&amp;';
    }

    return '<a href="'.rex_getUrl($value, $format['clang'], $format['params'], $format['divider']).'"'.$format['attr'].'>'.$value.'</a>';
  }
}

/**
 * Returns the truncated $string
 *
 * @param $string String Searchstring
 * @param $start String Suffix to search for
 */
function truncate($string, $length = 80, $etc = '...', $break_words = false)
{
  if ($length == 0)
    return '';

  if (strlen($string) > $length)
  {
    $length -= strlen($etc);
    if (!$break_words)
      $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length +1));

    return substr($string, 0, $length).$etc;
  }
  else
    return $string;
}