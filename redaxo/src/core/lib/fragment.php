<?php

/**
 * Methods declared here are available to be called from within a fragment-file, even if not public:.
 *
 * @phpstan-method void   subfragment(string $filename, array $params = [])
 * @phpstan-method string getSubfragment(string $filename, array $params = [])
 * @phpstan-method string i18n(string $key, ...$replacements)
 * @phpstan-method mixed  escape($value, $strategy = 'html')
 *
 * @package redaxo\core
 */
class rex_fragment
{
    /**
     * filename of the actual fragmentfile.
     *
     * @var string
     */
    private $filename;

    /**
     * key-value pair which represents all variables defined inside the fragment.
     *
     * @var array<string, mixed>
     */
    private $vars;

    /**
     * another fragment which can optionaly be used to decorate the current fragment.
     *
     * @var rex_fragment
     */
    private $decorator;

    /**
     * array which contains all folders in which fragments will be searched for at runtime.
     *
     * @var string[]
     */
    private static $fragmentDirs = [];

    /**
     * Creates a fragment with the given variables.
     *
     * @param array<string, mixed> $vars A array of key-value pairs to pass as local parameters
     */
    public function __construct(array $vars = [])
    {
        $this->vars = $vars;
    }

    /**
     * Returns the value of the given variable $name.
     *
     * @param string $name    Variable name
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public function getVar($name, $default = null)
    {
        if (isset($this->vars[$name])) {
            return $this->vars[$name];
        }

        return $default;
    }

    /**
     * Set the variable $name to the given value.
     *
     * @param string $name   The name of the variable
     * @param mixed  $value  The value for the variable
     * @param bool   $escape Flag which indicates if the value should be escaped or not
     *
     * @throws InvalidArgumentException
     *
     * @return $this
     */
    public function setVar($name, $value, $escape = true)
    {
        if (null === $name) {
            throw new InvalidArgumentException('Expecting $name to be not null!');
        }

        if ($escape) {
            $this->vars[$name] = $this->escape($value);
        } else {
            $this->vars[$name] = $value;
        }

        return $this;
    }

    /**
     * Parses the variables of the fragment into the file $filename.
     *
     * @param string $filename the filename of the fragment to parse
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function parse($filename)
    {
        if (!is_string($filename)) {
            throw new InvalidArgumentException(sprintf('Expecting $filename to be a string, %s given!', gettype($filename)));
        }

        $this->filename = $filename;

        foreach (self::$fragmentDirs as $fragDir) {
            $fragment = $fragDir . $filename;
            if (is_readable($fragment)) {
                $content = rex_timer::measure('Fragment: '.$filename, function () use ($fragment) {
                    ob_start();
                    require $fragment;

                    return rex_type::string(ob_get_clean());
                });

                if ($this->decorator) {
                    $this->decorator->setVar('rexDecoratedContent', $content, false);
                    $content = $this->decorator->parse($this->decorator->filename);
                }

                return $content;
            }
        }

        throw new InvalidArgumentException(sprintf('Fragmentfile "%s" not found!', $filename));
    }

    /**
     * Decorate the current fragment, with another fragment.
     * The decorated fragment receives the parameters which are passed to this method.
     *
     * @param string $filename The filename of the fragment used for decoration
     * @param array<string, mixed> $params A array of key-value pairs to pass as parameters
     *
     * @return $this
     */
    public function decorate($filename, array $params)
    {
        $this->decorator = new self($params);
        $this->decorator->filename = $filename;

        return $this;
    }

    // -------------------------- in-fragment helpers

    /**
     * Escapes the value $val for proper use in the gui.
     *
     * @template T
     * @param T $value The value to escape
     * @param string $strategy One of "html", "html_attr", "css", "js", "url"
     * @psalm-param 'html'|'html_simplified'|'html_attr'|'js'|'css'|'url' $strategy
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     * @psalm-return (T is Stringable ? string : T)
     */
    protected function escape($value, $strategy = 'html')
    {
        return rex_escape($value, $strategy);
    }

    /**
     * Returns the content of a Subfragment from within a fragment.
     *
     * The Subfragment gets all variables of the current fragment, plus optional overrides from $params
     *
     * @param string $filename The filename of the fragment to use
     * @param array<string, mixed> $params A array of key-value pairs to pass as local parameters
     */
    protected function getSubfragment(string $filename, array $params = []): string
    {
        $fragment = new self(array_merge($this->vars, $params));
        return $fragment->parse($filename);
    }

    /**
     * Include a Subfragment from within a fragment.
     *
     * The Subfragment gets all variables of the current fragment, plus optional overrides from $params
     *
     * @param string $filename The filename of the fragment to use
     * @param array<string, mixed> $params A array of key-value pairs to pass as local parameters
     * @return void
     */
    protected function subfragment($filename, array $params = [])
    {
        echo $this->getSubfragment($filename, $params);
    }

    /**
     * Translate the given key $key.
     *
     * @param string     $key             The key to translate
     * @param string|int ...$replacements A arbritary number of strings used for interpolating within the resolved message
     *
     * @return string Translation for the key
     */
    protected function i18n($key, ...$replacements)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(sprintf('Expecting $key to be a string, %s given!', gettype($key)));
        }

        return rex_i18n::msg($key, ...$replacements);
    }

    /**
     * Magic getter to reference variables from within the fragment.
     *
     * @param string $name The name of the variable to get
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->vars[$name]) || array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        }

        trigger_error(sprintf('Undefined variable "%s" in rex_fragment "%s"', $name, $this->filename), E_USER_WARNING);

        return null;
    }

    /**
     * Magic method to check if a variable is set.
     *
     * @param string $name The name of the variable to check
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->vars[$name]) || array_key_exists($name, $this->vars);
    }

    /**
     * Add a path to the fragment search path.
     *
     * @param string $dir A path to a directory where fragments can be found
     * @return void
     */
    public static function addDirectory($dir)
    {
        // add the new directory in front of the already know dirs,
        // so a later caller can override core settings/fragments
        $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        array_unshift(self::$fragmentDirs, $dir);
    }
}
