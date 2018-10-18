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
    private static $loaded = [];
    /**
     * @var string|null
     */
    private static $locale = null;
    /**
     * @var string[][]
     */
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

        if (empty(self::$loaded[$locale])) {
            self::loadAll($locale);
        }

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
     * Returns the current locale, e.g. de_de.
     *
     * @return string The current locale
     */
    public static function getLocale()
    {
        return self::$locale;
    }

    /**
     * Returns the current language, e.g. "de".
     *
     * @return string The current language
     */
    public static function getLanguage()
    {
        list($lang, $country) = explode('_', self::$locale, 2);
        return $lang;
    }

    /**
     * Adds a directory with lang files.
     *
     * @param string $dir Path to the directory
     */
    public static function addDirectory($dir)
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        if (in_array($dir, self::$directories, true)) {
            return;
        }

        self::$directories[] = $dir;

        foreach (self::$loaded as $locale => $_) {
            self::loadFile($dir, $locale);
        }
    }

    /**
     * Returns the translation htmlspecialchared for the given key.
     *
     * @param string $key             A Language-Key
     * @param string ...$replacements A arbritary number of strings used for interpolating within the resolved message
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
     * @param string $key             A Language-Key
     * @param string ...$replacements A arbritary number of strings used for interpolating within the resolved message
     *
     * @return string Translation for the key
     */
    public static function rawMsg($key)
    {
        return self::getMsg($key, false, func_get_args());
    }

    /**
     * Returns the translation htmlspecialchared for the given key and locale.
     *
     * @param string $key             A Language-Key
     * @param string $locale          A Locale
     * @param string ...$replacements A arbritary number of strings used for interpolating within the resolved message
     *
     * @return string Translation for the key
     */
    public static function msgInLocale($key, $locale)
    {
        $args = func_get_args();
        $args[1] = $key;
        // for BC we need to strip the 1st arg
        array_shift($args);
        return self::getMsg($key, true, $args, $locale);
    }

    /**
     * Returns the translation for the given key and locale.
     *
     * @param string $key             A Language-Key
     * @param string $locale          A Locale
     * @param string ...$replacements A arbritary number of strings used for interpolating within the resolved message
     *
     * @return string Translation for the key
     */
    public static function rawMsgInLocale($key, $locale)
    {
        $args = func_get_args();
        $args[1] = $key;
        // for BC we need to strip the 1st arg
        array_shift($args);
        return self::getMsg($key, false, $args, $locale);
    }

    /**
     * Returns the message fallback for a missing key in main locale.
     *
     * @param string $key
     * @param array  $args
     * @param string $locale A Locale
     *
     * @return string
     */
    private static function getMsgFallback($key, array $args, $locale)
    {
        $fallback = "[translate:$key]";

        $msg = rex_extension::registerPoint(new rex_extension_point('I18N_MISSING_TRANSLATION', $fallback, [
            'key' => $key,
            'args' => $args,
        ]));

        if ($msg !== $fallback) {
            return $msg;
        }

        foreach (rex::getProperty('lang_fallback', []) as $fallbackLocale) {
            if ($locale === $fallbackLocale) {
                continue;
            }

            if (empty(self::$loaded[$fallbackLocale])) {
                self::loadAll($fallbackLocale);
            }

            if (isset(self::$msg[$fallbackLocale][$key])) {
                return self::$msg[$fallbackLocale][$key];
            }
        }

        return $fallback;
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
        return isset(self::$msg[self::$locale][$key]);
    }

    /**
     * Returns the translation for the given key.
     *
     * @param string $key
     * @param bool   $htmlspecialchars
     * @param array  $args
     * @param string $locale           A Locale
     *
     * @return mixed
     */
    private static function getMsg($key, $htmlspecialchars, array $args, $locale = null)
    {
        if (!self::$locale) {
            self::$locale = rex::getProperty('lang');
        }

        if (!$locale) {
            $locale = self::$locale;
        }

        if (empty(self::$loaded[$locale])) {
            self::loadAll($locale);
        }

        if (isset(self::$msg[$locale][$key])) {
            $msg = self::$msg[$locale][$key];
        } else {
            $msg = self::getMsgFallback($key, $args, $locale);
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

        $msg = preg_replace($patterns, $replacements, $msg);

        if ($htmlspecialchars) {
            $msg = rex_escape($msg);
            $msg = preg_replace('@&lt;(/?(?:b|i|code|kbd|var)|br ?/?)&gt;@i', '<$1>', $msg);
        }

        return $msg;
    }

    /**
     * Checks if there is a translation for the given key in current language or any fallback language.
     *
     * @param string $key Key
     *
     * @return bool TRUE on success, else FALSE
     */
    public static function hasMsgOrFallback($key)
    {
        if (isset(self::$msg[self::$locale][$key])) {
            return true;
        }

        foreach (rex::getProperty('lang_fallback', []) as $locale) {
            if (self::$locale === $locale) {
                continue;
            }

            if (empty(self::$loaded[$locale])) {
                self::loadAll($locale);
            }

            if (isset(self::$msg[$locale][$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a new translation to the catalogue.
     *
     * @param string $key Key
     * @param string $msg Message for the key
     */
    public static function addMsg($key, $msg)
    {
        self::$msg[self::$locale][$key] = $msg;
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
                if ($use_htmlspecialchars) {
                    return self::msg(substr($text, $transKeyLen));
                }
                return self::rawMsg(substr($text, $transKeyLen));
            }
            // cuf() required for php5 compat to support 'class::method' like callables
            return call_user_func($i18nFunction, substr($text, $transKeyLen));
        }
        if ($use_htmlspecialchars) {
            return rex_escape($text);
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
                if (is_string($value)) {
                    $array[$key] = self::translate($value, $use_htmlspecialchars, $i18nFunction);
                } else {
                    $array[$key] = self::translateArray($value, $use_htmlspecialchars, $i18nFunction);
                }
            }
            return $array;
        }
        if (is_string($array)) {
            return self::translate($array, $use_htmlspecialchars, $i18nFunction);
        }
        if (null === $array || is_scalar($array)) {
            return $array;
        }
        throw new InvalidArgumentException('Expecting $text to be a String or Array of Scalar, "' . gettype($array) . '" given!');
    }

    /**
     * Loads the translation definitions of the given file.
     *
     * @param string $dir    Path to the directory
     * @param string $locale Locale
     */
    private static function loadFile($dir, $locale)
    {
        $file = $dir.DIRECTORY_SEPARATOR.$locale.'.lang';

        if (
            ($content = rex_file::get($file)) &&
            preg_match_all('/^([^=\s]+)\h*=\h*(\S.*)(?<=\S)/m', $content, $matches, PREG_SET_ORDER)
        ) {
            foreach ($matches as $match) {
                self::$msg[$locale][$match[1]] = $match[2];
            }
        }
    }

    /**
     * Loads all translation defintions.
     *
     * @param string $locale Locale
     */
    private static function loadAll($locale)
    {
        foreach (self::$directories as $dir) {
            self::loadFile($dir, $locale);
        }

        self::$loaded[$locale] = true;
    }
}
