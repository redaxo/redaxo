<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob
 */

abstract class rex_cronjob
{
    /** @var array */
    private $params = [];
    /** @var string */
    private $message = '';

    /**
     * @template T of rex_cronjob
     *
     * @param class-string<T> $class
     *
     * @return class-string<T>|T
     */
    final public static function factory($class)
    {
        if (!rex_autoload::autoload($class)) {
            return $class;
        }

        return new $class();
    }

    /**
     * @param string $key
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @param string $key
     */
    public function getParam($key, $default = null)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }

        return $default;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    public function __set($key, $value)
    {
        $this->setParam($key, $value);
    }

    public function __get($key)
    {
        return $this->getParam($key);
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function hasMessage()
    {
        return !empty($this->message);
    }

    /**
     * @return bool true on successfull execution, false on error
     */
    abstract public function execute();

    /**
     * @return string
     */
    public function getTypeName()
    {
        // returns the name of the cronjob type
        return $this->getType();
    }

    /**
     * @return string
     */
    final public function getType()
    {
        return get_class($this);
    }

    /**
     * @return string[]
     */
    public function getEnvironments()
    {
        // returns an array of environments in which the cronjob is available
        return ['frontend', 'backend', 'script'];
    }

    /**
     * @return array
     */
    public function getParamFields()
    {
        // returns an array of parameters which are required for the cronjob
        return [];
    }
}
