<?php

namespace Redaxo\Core\Http;

use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Url;

/**
 * A generic implementation of ContextProvider.
 */
class Context implements ContextProviderInterface
{
    /**
     * Constructs a Context with the given global parameters.
     *
     * @param array<string, mixed> $globalParams A array containing only scalar values for key/value
     */
    public function __construct(
        private array $globalParams = [],
    ) {}

    public function getUrl(array $params = [])
    {
        // combine global params with local
        $params = array_merge($this->globalParams, $params);

        return Core::isBackend() ? Url::backendController($params) : Url::frontendController($params);
    }

    /**
     * Set a global parameter.
     *
     * @param string $name
     * @param mixed $value
     * @return void
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
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        return $this->globalParams[$name] ?? $default;
    }

    /**
     * Returns the global parameters.
     *
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        return $this->globalParams;
    }

    /**
     * Returns whether the given parameter exists.
     */
    public function hasParam(string $name): bool
    {
        return isset($this->globalParams[$name]);
    }

    /**
     * Removes a global parameter.
     */
    public function removeParam(string $name): void
    {
        unset($this->globalParams[$name]);
    }

    /**
     * @see ContextProviderInterface::getHiddenInputFields()
     */
    public function getHiddenInputFields(array $params = [])
    {
        // combine global params with local
        $params = array_merge($this->globalParams, $params);

        return self::array2inputStr($params);
    }

    /**
     * Returns a Context instance containing all GET and POST parameters.
     *
     * @return Context
     */
    public static function restore()
    {
        // $_REQUEST contains some server specific globals, therefore we merge GET and POST manually
        return new self($_GET + $_POST);
    }

    /**
     * Returns a Context instance containing all GET parameters.
     *
     * @return Context
     */
    public static function fromGet()
    {
        return new self($_GET);
    }

    /**
     * Returns a Context instance containing all POST parameters.
     *
     * @return Context
     */
    public static function fromPost()
    {
        return new self($_POST);
    }

    /**
     * Helper method to generate a html string with hidden input fields from an array key-value pairs.
     *
     * @param array $array The array which contains the key-value pairs for conversion
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
