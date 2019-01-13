<?php

/**
 * Generic interface for classes which provide urls.
 *
 * @author staabm
 *
 * @package redaxo\core
 */
interface rex_url_provider_interface
{
    /**
     * Returns a Url which contains the given parameters.
     *
     * @param array $params A scalar array containing key value pairs for the parameter and its value
     * @param bool  $escape Flag whether the argument separator "&" should be escaped (&amp;)
     *
     * @return string The generated Url
     */
    public function getUrl(array $params = [], $escape = true);
}

/**
 * Generic interface for classes which provide a complete rex-context.
 * A rex-context consists of a set of parameters which may get passed using urls (via parameter) or forms (via hidden input fields).
 *
 * @author staabm
 *
 * @package redaxo\core
 */
interface rex_context_provider_interface extends rex_url_provider_interface
{
    /**
     * Returns a html string containg hidden input fields for the given parameters.
     *
     * @param array $params A array containing key value pairs for the parameter and its value
     *
     * @return string The generated html source containing the hidden input fields
     */
    public function getHiddenInputFields(array $params = []);
}

/**
 * A generic implementiation of rex_context_provider.
 *
 * @author staabm
 *
 * @package redaxo\core
 */
class rex_context implements rex_context_provider_interface
{
    private $globalParams;

    /**
     * Constructs a rex_context with the given global parameters.
     *
     * @param array $globalParams A array containing only scalar values for key/value
     */
    public function __construct(array $globalParams = [])
    {
        $this->globalParams = $globalParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(array $params = [], $escape = true)
    {
        // combine global params with local
        $_params = array_merge($this->globalParams, $params);

        return rex::isBackend() ? rex_url::backendController($_params, $escape) : rex_url::frontendController($_params, $escape);
    }

    /**
     * Set a global parameter.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParam($name, $value)
    {
        $this->globalParams[$name] = $value;
    }

    /**
     * Returns the value associated with the given parameter $name.
     * When no value is set, $default will be returned.
     *
     * @param string $name
     * @param string $default
     *
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        return isset($this->globalParams[$name]) ? $this->globalParams[$name] : $default;
    }

    /**
     * @see rex_context_provider::getHiddenInputFields()
     */
    public function getHiddenInputFields(array $params = [])
    {
        // combine global params with local
        $_params = array_merge($this->globalParams, $params);

        return self::array2inputStr($_params);
    }

    /**
     * Returns a rex_context instance containing all GET and POST parameters.
     *
     * @return self
     */
    public static function restore()
    {
        // $_REQUEST contains some server specific globals, therefore we merge GET and POST manually
        return new self($_GET + $_POST);
    }

    /**
     * Returns a rex_context instance containing all GET parameters.
     *
     * @return self
     */
    public static function fromGet()
    {
        return new self($_GET);
    }

    /**
     * Returns a rex_context instance containing all POST parameters.
     *
     * @return self
     */
    public static function fromPost()
    {
        return new self($_POST);
    }

    /**
     * Helper method to generate a html string with hidden input fields from an array key-value pairs.
     *
     * @param array $array The array which contains the key-value pairs for convertion
     *
     * @return string
     */
    private static function array2inputStr(array $array)
    {
        $inputString = '';
        foreach ($array as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $valName => $valVal) {
                    $inputString .= '<input type="hidden" name="' . rex_escape($name) . '[' . rex_escape($valName) . ']" value="' . rex_escape($valVal) . '" />';
                }
            } else {
                $inputString .= '<input type="hidden" name="' . rex_escape($name) . '" value="' . rex_escape($value) . '" />';
            }
        }

        return $inputString;
    }
}
