<?php

/**
 * Extension Point Class.
 *
 * @template T
 *
 * @author gharlan
 *
 * @package redaxo\core
 *
 * @psalm-taint-specialize
 */
class rex_extension_point
{
    /** @var string */
    private $name;
    /** @var T */
    private $subject;
    /** @var array */
    private $params = [];
    /** @var array */
    private $extensionParams = [];
    /** @var bool */
    private $readonly = false;

    /**
     * @param string $name
     * @param T $subject
     * @param bool $readonly
     */
    public function __construct($name, $subject = null, array $params = [], $readonly = false)
    {
        $this->name = $name;
        $this->subject = $subject;
        $this->params = $params;
        $this->readonly = $readonly;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the subject.
     *
     * @param T $subject
     * @throws rex_exception
     * @return void
     */
    public function setSubject($subject)
    {
        if ($this->isReadonly()) {
            throw new rex_exception('Subject can\'t be adjusted in readonly extension points');
        }
        $this->subject = $subject;
    }

    /**
     * Returns the subject.
     *
     * @return T
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets a param.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @throws rex_exception
     * @return void
     */
    public function setParam($key, $value)
    {
        if ($this->isReadonly()) {
            throw new rex_exception('Params can\'t be adjusted in readonly extension points');
        }
        $this->params[$key] = $value;
    }

    /**
     * Sets the specific params for the next extension.
     * @return void
     */
    public function setExtensionParams(array $params)
    {
        $this->extensionParams = $params;
    }

    /**
     * Returns whether the given param exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasParam($key)
    {
        return isset($this->params[$key]) || isset($this->extensionParams[$key]);
    }

    /**
     * Returns the param for the given key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        if (isset($this->extensionParams[$key])) {
            return $this->extensionParams[$key];
        }
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        return $default;
    }

    /**
     * Returns all params.
     *
     * @return array
     */
    public function getParams()
    {
        return array_merge($this->params, $this->extensionParams);
    }

    /**
     * Returns whether the extension point is readonly.
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->readonly;
    }
}
