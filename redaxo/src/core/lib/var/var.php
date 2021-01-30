<?php

/**
 * Abstract baseclass for REX_VARS.
 *
 * @package redaxo\core
 */
abstract class rex_var
{
    public const ENV_FRONTEND = 1;
    public const ENV_BACKEND = 2;
    public const ENV_INPUT = 4;
    public const ENV_OUTPUT = 8;

    /**
     * @psalm-var null|array<string, class-string<self>>
     */
    private static $vars = [];
    private static $env = null;
    private static $context = null;
    private static $contextData = null;

    private $args = [];

    private static $variableIndex = 0;

    /**
     * Parses all REX_VARs in the given content.
     *
     * @param string $content     Content
     * @param int    $env         Environment
     * @param string $context     Context
     * @param mixed  $contextData Context data
     *
     * @return string
     */
    public static function parse($content, $env = null, $context = null, $contextData = null)
    {
        $env = (int) $env;

        if (self::ENV_INPUT != ($env & self::ENV_INPUT)) {
            $env = $env | self::ENV_OUTPUT;
        }

        self::$env = $env;
        self::$context = $context;
        self::$contextData = $contextData;

        self::$variableIndex = 0;

        $tokens = token_get_all($content);
        $countTokens = count($tokens);
        $content = '';
        for ($i = 0; $i < $countTokens; ++$i) {
            $token = $tokens[$i];
            if (is_string($token)) {
                $content .= $token;
                continue;
            }

            if (!in_array($token[0], [T_INLINE_HTML, T_CONSTANT_ENCAPSED_STRING, T_STRING, T_START_HEREDOC])) {
                $content .= $token[1];
                continue;
            }

            $add = $token[1];
            switch ($token[0]) {
                case T_INLINE_HTML:
                    $format = '<?= %s@@@INLINE_HTML_REPLACEMENT_END@@@';
                    $add = self::replaceVars($add, $format);
                    $add = preg_replace_callback('/@@@INLINE_HTML_REPLACEMENT_END@@@(\r?\n?)/', static function (array $match) {
                        return $match[1]
                            ? ', "'.addcslashes($match[1], "\r\n").'" ?>'.$match[1]
                            : ' ?>';
                    }, $add);
                    break;

                case T_CONSTANT_ENCAPSED_STRING:
                    $format = '"' == $token[1][0] ? '" . %s . "' : "' . %s . '";
                    $add = self::replaceVars($add, $format, false, $token[1][0]);

                    $start = substr($add, 0, 5);
                    $end = substr($add, -5);
                    if ('"" . ' == $start || "'' . " == $start) {
                        $add = substr($add, 5);
                    }
                    if (' . ""' == $end || " . ''" == $end) {
                        $add = substr($add, 0, -5);
                    }
                    break;

                case T_STRING:
                    while (isset($tokens[++$i])
                        && (is_string($tokens[$i]) && in_array($tokens[$i], ['=', '[', ']'])
                            || in_array($tokens[$i][0], [T_WHITESPACE, T_STRING, T_CONSTANT_ENCAPSED_STRING, T_LNUMBER, T_ISSET]))
                    ) {
                        $add .= is_string($tokens[$i]) ? $tokens[$i] : $tokens[$i][1];
                    }
                    --$i;
                    $add = self::replaceVars($add);
                    break;

                case T_START_HEREDOC:
                    while (isset($tokens[++$i]) && (is_string($tokens[$i]) || T_END_HEREDOC != $tokens[$i][0])) {
                        $add .= is_string($tokens[$i]) ? $tokens[$i] : $tokens[$i][1];
                    }
                    --$i;
                    if (preg_match("/'(.*)'/", $token[1], $match)) { // nowdoc
                        $format = "\n" . $match[1] . "\n. %s . <<<'" . $match[1] . "'\n";
                        $add = self::replaceVars($add, $format);
                    } else { // heredoc
                        $add = self::replaceVars($add, '{%s}', true);
                    }
                    break;
            }

            $content .= $add;
        }
        return $content;
    }

    /**
     * Returns a rex_var object for the given var name.
     *
     * @param string $var
     */
    private static function getVar($var): ?self
    {
        if (!isset(self::$vars[$var])) {
            /** @var class-string $class */
            $class = 'rex_var_' . strtolower(substr($var, 4));
            if (!class_exists($class) || !is_subclass_of($class, self::class)) {
                return null;
            }
            self::$vars[$var] = $class;
        }
        $class = self::$vars[$var];
        return new $class();
    }

    /**
     * Replaces the REX_VARs.
     *
     * @param string $content
     * @param string $format
     * @param bool   $useVariables
     * @param string $stripslashes
     *
     * @return mixed|string
     */
    private static function replaceVars($content, $format = '%s', $useVariables = false, $stripslashes = null)
    {
        $matches = self::getMatches($content);

        if (empty($matches)) {
            return $content;
        }

        $iterator = new AppendIterator();
        $iterator->append(new ArrayIterator($matches));
        $variables = [];
        $replacements = [];

        foreach ($iterator as $match) {
            if (isset($replacements[$match[0]])) {
                continue;
            }

            $var = self::getVar($match[1]);
            $replaced = false;

            if (null !== $var) {
                $args = str_replace(['\[', '\]'], ['@@@OPEN_BRACKET@@@', '@@@CLOSE_BRACKET@@@'], $match[2]);
                if ($stripslashes) {
                    $args = str_replace(['\\' . $stripslashes, '\\' . $stripslashes], $stripslashes, $args);
                }
                $var->setArgs($args);
                if (false !== ($output = $var->getGlobalArgsOutput())) {
                    $output .= str_repeat("\n", max(0, substr_count($match[0], "\n") - substr_count($output, "\n") - substr_count($format, "\n")));
                    if ($useVariables) {
                        $replace = '$__rex_var_content_' . ++self::$variableIndex;
                        $variables[] = '/* '. $match[0] .' */ ' . $replace . ' = ' . $output;
                    } else {
                        $replace = '/* '. $match[0] .' */ '. $output;
                    }

                    $replacements[$match[0]] = sprintf($format, $replace);
                    $replaced = true;
                }
            }

            if (!$replaced && $matches = self::getMatches($match[2])) {
                $iterator->append(new ArrayIterator($matches));
            }
        }

        if ($replacements) {
            $content = strtr($content, $replacements);
        }

        if ($useVariables && !empty($variables)) {
            $content = 'rex_var::nothing(' . implode(', ', $variables) . ') . ' . $content;
        }

        return $content;
    }

    /**
     * Returns the REX_VAR matches.
     *
     * @param string $content
     *
     * @return array
     */
    private static function getMatches($content)
    {
        preg_match_all('/(REX_[A-Z0-9_]+)\[((?:[^\[\]]|\\\\[\[\]]|(?R))*)(?<!\\\\)\]/s', $content, $matches, PREG_SET_ORDER);
        return $matches;
    }

    /**
     * Sets the arguments.
     *
     * @param string $argString
     */
    private function setArgs($argString)
    {
        $this->args = rex_string::split($argString);
    }

    /**
     * Checks whether the given arguments exists.
     *
     * @param string $key
     * @param bool   $defaultArg
     *
     * @return bool
     */
    protected function hasArg($key, $defaultArg = false)
    {
        return isset($this->args[$key]) || $defaultArg && isset($this->args[0]);
    }

    /**
     * Returns the argument.
     *
     * @param string          $key
     * @param null|string|int $default
     * @param bool            $defaultArg
     *
     * @return null|string|int
     *
     * @template T as null|string|int
     * @phpstan-template T
     * @psalm-param T $default
     * @psalm-return string|T
     */
    protected function getArg($key, $default = null, $defaultArg = false)
    {
        if (!$this->hasArg($key, $defaultArg)) {
            return $default;
        }
        return $this->args[$key] ?? $this->args[0];
    }

    /**
     * Returns the (recursive) parsed argument.
     *
     * @param string          $key
     * @param null|string|int $default
     * @param bool            $defaultArg
     *
     * @return int|null|string
     *
     * @template T as null|string|int
     * @phpstan-template T
     * @psalm-param T $default
     * @psalm-return string|T
     */
    protected function getParsedArg($key, $default = null, $defaultArg = false)
    {
        if (!$this->hasArg($key, $defaultArg)) {
            return $default;
        }
        $arg = $this->args[$key] ?? $this->args[0];
        $begin = '<<<addslashes>>>';
        $end = '<<</addslashes>>>';
        $arg = $begin . self::replaceVars($arg, $end . "' . %s . '" . $begin) . $end;
        $arg = preg_replace_callback("@$begin(.*)$end@Us", static function ($match) {
            return addcslashes($match[1], "\'");
        }, $arg);
        $arg = str_replace(['@@@OPEN_BRACKET@@@', '@@@CLOSE_BRACKET@@@'], ['[', ']'], $arg);
        return is_numeric($arg) ? $arg : "'$arg'";
    }

    /**
     * Checks whether the given envirenment is active.
     *
     * @param int $env Environment
     *
     * @return bool
     */
    protected function environmentIs($env)
    {
        return (self::$env & $env) == $env;
    }

    /**
     * Returns the context.
     *
     * @return string
     */
    protected function getContext()
    {
        return self::$context;
    }

    /**
     * Returns the context data.
     *
     * @return mixed
     */
    protected function getContextData()
    {
        return self::$contextData;
    }

    /**
     * Returns the output.
     *
     * @return false|string
     */
    abstract protected function getOutput();

    /**
     * Quotes the string for php context.
     *
     * @param string $string
     *
     * @return string
     */
    protected static function quote($string)
    {
        $string = addcslashes($string, "\\'");
        $string = preg_replace('/\v+/', '\' . "$0" . \'', $string);
        $string = addcslashes($string, "\r\n");
        return "'" . $string . "'";
    }

    /**
     * Returns the output in consideration of the global args.
     *
     * @return false|string
     */
    private function getGlobalArgsOutput()
    {
        if (false === ($content = $this->getOutput())) {
            return false;
        }

        if ($this->hasArg('callback')) {
            $args = [
                "'var' => 'REX" . strtoupper(substr(static::class, 7)) . "'",
                "'class' => '" . static::class . "'",
                "'subject' => " . $content,
            ];
            foreach ($this->args as $key => $value) {
                $args[] = "'$key' => " . $this->getParsedArg($key);
            }
            $args = '[' . implode(', ', $args) . ']';
            return 'call_user_func(' . $this->getParsedArg('callback') . ', ' . $args . ')';
        }

        $prefix = $this->hasArg('prefix') ? $this->getParsedArg('prefix') . ' . ' : '';
        $suffix = $this->hasArg('suffix') ? ' . ' . $this->getParsedArg('suffix') : '';
        $instead = $this->hasArg('instead');
        $ifempty = $this->hasArg('ifempty');
        if ($prefix || $suffix || $instead || $ifempty) {
            if ($instead) {
                $if = $content;
                $then = $this->getParsedArg('instead');
            } else {
                $if = '$__rex_var_content_' . ++self::$variableIndex . ' = ' . $content;
                $then = '$__rex_var_content_' . self::$variableIndex;
            }
            if ($ifempty) {
                return $prefix . '((' . $if . ') ? ' . $then . ' : ' . $this->getParsedArg('ifempty') . ')' . $suffix;
            }
            return '((' . $if . ') ? ' . $prefix . $then . $suffix . " : '')";
        }
        return $content;
    }

    /**
     * Converts a REX_VAR content to a PHP array.
     *
     * @param string $value
     *
     * @return array|null
     */
    public static function toArray($value)
    {
        $value = json_decode(htmlspecialchars_decode($value, ENT_QUOTES), true);
        return is_array($value) ? $value : null;
    }

    /**
     * Returns empty string.
     *
     * @return string
     */
    public static function nothing()
    {
        return '';
    }
}
