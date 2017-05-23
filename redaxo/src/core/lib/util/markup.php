<?php

/**
 * Markup parser class
 *
 * @author gharlan
 * @package redaxo\core
 */
class rex_markup
{
    /**
     * @var callable[]
     */
    private static $parsers = [];

    /**
     * @var int[]
     */
    private static $fileExtensions = [];

    /**
     * Parses a file, the markup type is select by the file extension
     *
     * @param string $file
     * @param array  $options
     * @return string
     * @throws InvalidArgumentException
     */
    public static function parseFile($file, array $options = [])
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist!', $file));
        }
        return self::parse(rex_file::get($file), rex_file::extension($file), $options);
    }

    /**
     * Parses content by the given markup type
     *
     * @param string $content
     * @param string $markup
     * @param array  $options
     * @return string
     */
    public static function parse($content, $markup, array $options = [])
    {
        $markup = strtolower($markup);
        if (isset(self::$fileExtensions[$markup])) {
            return call_user_func(self::$parsers[self::$fileExtensions[$markup]], $content, $options);
        }
        return $content;
    }

    /**
     * Registers a markup type
     *
     * @param string[] $fileExtensions
     * @param callable $parser
     */
    public static function register(array $fileExtensions, callable $parser)
    {
        $id = count(self::$fileExtensions);
        self::$parsers[$id] = $parser;
        foreach ($fileExtensions as $extension) {
            self::$fileExtensions[strtolower($extension)] = $id;
        }
    }

    /**
     * Checks whether the given markup type is registered
     *
     * @param string $markup
     * @return bool
     */
    public function isRegistered($markup)
    {
        return isset(self::$fileExtensions[strtolower($markup)]);
    }
}
