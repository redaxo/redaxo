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
    /** @var array<string, mixed> */
    private array $extensionParams = [];

    /**
     * @param string $name
     * @param T $subject
     * @param array<string, mixed> $params
     * @param bool $readonly
     */
    public function __construct(
        private $name,
        private $subject = null,
        private array $params = [],
        private $readonly = false,
    ) {}

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
     * @param mixed $value
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
     * @param array<string, mixed> $params
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
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        return $this->extensionParams[$key] ?? $this->params[$key] ?? $default;
    }

    /**
     * Returns all params.
     *
     * @return array<string, mixed>
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
