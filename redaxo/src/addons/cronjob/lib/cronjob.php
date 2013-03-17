<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob
 */

abstract class rex_cronjob
{
    private $params = [];
    private $message = '';

    final public static function factory($class)
    {
        if (!rex_autoload::autoload($class))
            return $class;

        return new $class();
    }

    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getParam($key, $default = null)
    {
        if (isset($this->params[$key]))
            return $this->params[$key];

        return $default;
    }

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

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function hasMessage()
    {
        return !empty($this->message);
    }

    abstract public function execute();

    public function getTypeName()
    {
        // returns the name of the cronjob type
        return $this->getType();
    }

    final public function getType()
    {
        return get_class($this);
    }

    public function getEnvironments()
    {
        // returns an array of environments in which the cronjob is available
        return ['frontend', 'backend'];
    }

    public function getParamFields()
    {
        // returns an array of parameters which are required for the cronjob
        return [];
    }
}
