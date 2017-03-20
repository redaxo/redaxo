<?php

/**
 * Class for internationalization.
 *
 * @package redaxo\core
 */
class rex_i18n
{
    private static $locales = [];
    private static $directories = [];
    private static $loaded = false;
    private static $locale = null;
    private static $msg = [];

    /**
     * Switches the current locale.
     *
     * @param string $locale       The new locale
     * @param bool   $phpSetLocale When TRUE, php function setlocale() will be called
     *
     * @return string The last locale
     */
    public static function setLocale($locale, $phpSetLocale = true)
    {
        $saveLocale = self::$locale;
        self::$locale = $locale;

        self::loadAll();

        if ($phpSetLocale) {
            $locales = [];
            foreach (explode(',', trim(self::msg('setlocale'))) as $locale) {
                $locales[] = $locale . '.UTF-8';
                $locales[] = $locale . '.UTF8';
                $locales[] = $locale . '.utf-8';
                $locales[] = $locale . '.utf8';
                $locales[] = $locale;
            }

            setlocale(LC_ALL, $locales);
        }

        return $saveLocale;
    }

    /**
     * Returns the current locale.
     *
     * @return string The current locale
     */
    public static function getLocale()
    {
        return self::$locale;
    }

    /**
     * Adds a directory with lang files.
     *
     * @param string $dir Path to the directory
     */
    public static function addDirectory($dir)
    {
        self::$directories[] = rtrim($dir, DIRECTORY_SEPARATOR);

        if (self::$loaded) {
            self::loadFile($dir . DIRECTORY_SEPARATOR . self::$locale . '.lang');
        }
    }

    /**
     * Returns the translation htmlspecialchared for the given key.
     *
     * @param string $key Key
     *
     * @return string Translation for the key
     */
    public static function msg($key)
    {
        return self::getMsg($key, true, func_get_args());
    }

    /**
     * Returns the translation for the given key.
     *
     * @param string $key Key
     *
     * @return string Translation for the key
     */
    public static function rawMsg($key)
    {
        return self::getMsg($key, false, func_get_args());
    }

    /**
     * Returns the translation for the given key.
     *
     * @param string $key
     * @param bool   $htmlspecialchars
     * @param array  $args
     *
     * @return mixed
     */
    private static function getMsg($key, $htmlspecialchars, array $args)
    {
        if (!self::$loaded) {
            self::loadAll();
        }

        if (self::hasMsg($key)) {
            $msg = self::$msg[$key];
        } else {
            $msg = "[translate:$key]";
            $msg = rex_extension::registerPoint(new rex_extension_point(
                'I18N_MISSING_TRANSLATION',
                $msg,
                [
                    'key' => $key,
                    'args' => $args,
                ]
            ));
        }

        if ($htmlspecialchars) {
            $msg = htmlspecialchars($msg);
            $msg = preg_replace('@&lt;(/?(?:b|i|code|kbd)|br ?/?)&gt;@i', '<$1>', $msg);
        }

        $patterns = [];
        $replacements = [];
        $argNum = count($args);
        if ($argNum > 1) {
            for ($i = 1; $i < $argNum; ++$i) {
                // zero indexed
                $patterns[] = '/\{' . ($i - 1) . '\}/';
                $replacements[] = $args[$i];
            }
        }
        return preg_replace($patterns, $replacements, $msg);
    }

    /**
     * Checks if there is a translation for the given key.
     *
     * @param string $key Key
     *
     * @return bool TRUE on success, else FALSE
     */
    public static function hasMsg($key)
    {
        return isset(self::$msg[$key]);
    }

    /**
     * Adds a new translation to the catalogue.
     *
     * @param string $key Key
     * @param string $msg Message for the key
     */
    public static function addMsg($key, $msg)
    {
        self::$msg[$key] = $msg;
    }

    /**
     * Returns the locales.
     *
     * @return array Array of Locales
     */
    public static function getLocales()
    {
        if (empty(self::$locales) && isset(self::$directories[0]) && is_readable(self::$directories[0])) {
            self::$locales = [];

            foreach (rex_finder::factory(self::$directories[0])->filesOnly() as $file) {
                if (preg_match("/^(\w+)\.lang$/", $file->getFilename(), $matches)) {
                    self::$locales[] = $matches[1];
                }
            }
        }

        return self::$locales;
    }

    /**
     * Translates the $text, if it begins with 'translate:', else it returns $text.
     *
     * @param string   $text                 The text for translation
     * @param bool     $use_htmlspecialchars Flag whether the translated text should be passed to htmlspecialchars()
     * @param callable $i18nFunction         Function that returns the translation for the i18n key
     *
     * @throws InvalidArgumentException
     *
     * @return string Translated text
     */
    public static function translate($text, $use_htmlspecialchars = true, callable $i18nFunction = null)
    {
        if (!is_string($text)) {
            throw new InvalidArgumentException('Expecting $text to be a String, "' . gettype($text) . '" given!');
        }

        $tranKey = 'translate:';
        $transKeyLen = strlen($tranKey);
        if (substr($text, 0, $transKeyLen) == $tranKey) {
            if (!$i18nFunction) {
                $i18nFunction = $use_htmlspecialchars ? 'self::msg' : 'self::rawMsg';
            }
            return call_user_func($i18nFunction, substr($text, $transKeyLen));
        } elseif ($use_htmlspecialchars) {
            return htmlspecialchars($text);
        }
        return $text;
    }

    /**
     * Translates all array elements.
     *
     * @param mixed    $array                The Array of Strings for translation
     * @param bool     $use_htmlspecialchars Flag whether the translated text should be passed to htmlspecialchars()
     * @param callable $i18nFunction         Function that returns the translation for the i18n key
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    public static function translateArray($array, $use_htmlspecialchars = true, callable $i18nFunction = null)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::translateArray($value, $use_htmlspecialchars, $i18nFunction);
            }
            return $array;
        } elseif (is_string($array)) {
            return self::translate($array, $use_htmlspecialchars, $i18nFunction);
        } elseif (null === $array || is_scalar($array)) {
            return $array;
        }
        throw new InvalidArgumentException('Expecting $text to be a String or Array of Scalar, "' . gettype($array) . '" given!');
    }

    /**
     * Loads the translation definitions of the given file.
     *
     * @param string $file Path to the file
     *
     * @return bool TRUE on success, FALSE on failure
     */
    private static function loadFile($file)
    {
        if (
            ($content = rex_file::get($file)) &&
            preg_match_all('/^([^=\s]+)\h*=\h*(.*)(?<=\S)/m', $content, $matches, PREG_SET_ORDER)
        ) {
            foreach ($matches as $match) {
                self::addMsg($match[1], $match[2]);
            }
        }
    }

    /**
     * Loads all translation defintions.
     */
    private static function loadAll()
    {
        self::$msg = [];
        if (!self::$locale) {
            self::$locale = rex::getProperty('lang');
        }
        foreach (self::$directories as $dir) {
            self::loadFile($dir . DIRECTORY_SEPARATOR . self::$locale . '.lang');
        }
        self::$loaded = true;
    }
}
