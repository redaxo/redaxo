<?php

/**
 * Escapes a variable.
 *
 * This function is adapted from code coming from Twig.
 * (c) Fabien Potencier
 * https://github.com/twigphp/Twig/blob/103cae817d68b56ddcb50c051a6ed7980d746409/lib/Twig/Extension/Core.php#L880-L1106
 *
 * @package redaxo\core
 *
 * @param mixed  $value    The value to escape
 * @param string $strategy One of "html", "html_attr", "css", "js", "url"
 *
 * @throws InvalidArgumentException
 *
 * @return mixed
 */
function rex_escape($value, $strategy = 'html')
{
    if (!is_string($value)) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = rex_escape($v, $strategy);
            }

            return $value;
        }

        if ($value instanceof \stdClass) {
            foreach (get_object_vars($value) as $k => $v) {
                $value->$k = rex_escape($v, $strategy);
            }

            return $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value;
        } else {
            return $value;
        }
    }

    $string = $value;

    switch ($strategy) {
        case 'html':
            // see http://php.net/htmlspecialchars
            return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        case 'js':
            // escape all non-alphanumeric characters
            // into their \xHH or \uHHHH representations

            if (0 === strlen($string) ? false : 1 !== preg_match('/^./su', $string)) {
                throw new InvalidArgumentException('The string to escape is not a valid UTF-8 string.');
            }

            $string = preg_replace_callback('#[^a-zA-Z0-9,\._]#Su', function ($matches) {
                $char = $matches[0];

                // \xHH
                if (!isset($char[1])) {
                    return '\\x'.strtoupper(substr('00'.bin2hex($char), -2));
                }

                // \uHHHH
                $char = mb_convert_encoding($char, 'UTF-16BE', 'UTF-8');
                $char = strtoupper(bin2hex($char));

                if (4 >= strlen($char)) {
                    return sprintf('\u%04s', $char);
                }

                return sprintf('\u%04s\u%04s', substr($char, 0, -4), substr($char, -4));
            }, $string);

            return $string;

        case 'css':
            if (0 == strlen($string) ? false : 1 !== preg_match('/^./su', $string)) {
                throw new InvalidArgumentException('The string to escape is not a valid UTF-8 string.');
            }

            $string = preg_replace_callback('#[^a-zA-Z0-9]#Su', function ($matches) {
                $char = $matches[0];

                // \xHH
                if (!isset($char[1])) {
                    $hex = ltrim(strtoupper(bin2hex($char)), '0');
                    if (0 === strlen($hex)) {
                        $hex = '0';
                    }

                    return '\\'.$hex.' ';
                }

                // \uHHHH
                $char = mb_convert_encoding($char, 'UTF-16BE', 'UTF-8');

                return '\\'.ltrim(strtoupper(bin2hex($char)), '0').' ';
            }, $string);

            return $string;

        case 'html_attr':
            if (0 == strlen($string) ? false : 1 !== preg_match('/^./su', $string)) {
                throw new InvalidArgumentException('The string to escape is not a valid UTF-8 string.');
            }

            $string = preg_replace_callback('#[^a-zA-Z0-9,\.\-_]#Su', function ($matches) {
                /**
                 * This function is adapted from code coming from Zend Framework.
                 *
                 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
                 * @license   http://framework.zend.com/license/new-bsd New BSD License
                 */
                /*
                 * While HTML supports far more named entities, the lowest common denominator
                 * has become HTML5's XML Serialisation which is restricted to the those named
                 * entities that XML supports. Using HTML entities would result in this error:
                 *     XML Parsing Error: undefined entity
                 */
                static $entityMap = [
                    34 => 'quot', /* quotation mark */
                    38 => 'amp',  /* ampersand */
                    60 => 'lt',   /* less-than sign */
                    62 => 'gt',   /* greater-than sign */
                ];

                $chr = $matches[0];
                $ord = ord($chr);

                /*
                 * The following replaces characters undefined in HTML with the
                 * hex entity for the Unicode replacement character.
                 */
                if (($ord <= 0x1f && $chr != "\t" && $chr != "\n" && $chr != "\r") || ($ord >= 0x7f && $ord <= 0x9f)) {
                    return '&#xFFFD;';
                }

                /*
                 * Check if the current character to escape has a name entity we should
                 * replace it with while grabbing the hex string of the character.
                 */
                if (strlen($chr) == 1) {
                    $hex = strtoupper(substr('00'.bin2hex($chr), -2));
                } else {
                    $chr = mb_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
                    $hex = strtoupper(substr('0000'.bin2hex($chr), -4));
                }

                $int = hexdec($hex);
                if (array_key_exists($int, $entityMap)) {
                    return sprintf('&%s;', $entityMap[$int]);
                }

                /*
                 * Per OWASP recommendations, we'll use hex entities for any other
                 * characters where a named entity does not exist.
                 */
                return sprintf('&#x%s;', $hex);
            }, $string);

            return $string;

        case 'url':
            return rawurlencode($string);

        default:
            throw new InvalidArgumentException(sprintf('Invalid escaping strategy "%s" (valid ones: "html", "html_attr", "css", "js", "url").', $strategy));
    }
}
