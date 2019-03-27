<?php

/**
 * Validator class.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_validator
{
    use rex_factory_trait;

    private $types = [];
    private $message;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        // noop
    }

    /**
     * Factory method.
     *
     * @return static
     */
    public static function factory()
    {
        $class = static::getFactoryClass();
        return new $class();
    }

    /**
     * Adds a validator.
     *
     * @param string      $type    Validator type (any static method name of this class)
     * @param null|string $message Message which is used if this validator type does not match
     * @param mixed       $option  Type specific option
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function add($type, $message = null, $option = null)
    {
        if (!method_exists($this, $type)) {
            throw new InvalidArgumentException('Unknown validator type: ' . $type);
        }
        $this->types[] = [$type, $message, $option];

        return $this;
    }

    /**
     * Checks whether the given value matches all added validators.
     *
     * @param string $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $this->message = null;
        foreach ($this->types as $type) {
            list($type, $message, $option) = $type;

            if ($value === '') {
                if (strtolower($type) !== 'notempty') {
                    continue;
                }
            }

            if (!$this->$type($value, $option)) {
                $this->message = $message;
                return false;
            }
        }
        return true;
    }

    /**
     * Returns the message.
     *
     * @return string[]
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Checks whether the value is not empty.
     *
     * @param string $value
     *
     * @return bool
     */
    public function notEmpty($value)
    {
        return 0 !== strlen($value);
    }

    /**
     * Checks whether the value is from the given type.
     *
     * @param string $value
     * @param string $type
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public function type($value, $type)
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return $this->match($value, '/^\d+$/');

            case 'float':
            case 'real':
                return is_numeric($value);

            default:
                throw new InvalidArgumentException('Unknown $type:' . $type);
        }
    }

    /**
     * Checks whether the value has the given min length.
     *
     * @param string $value
     * @param int    $minLength
     *
     * @return bool
     */
    public function minLength($value, $minLength)
    {
        return mb_strlen($value) >= $minLength;
    }

    /**
     * Checks whether the value has the given max value.
     *
     * @param string $value
     * @param int    $maxLength
     *
     * @return bool
     */
    public function maxLength($value, $maxLength)
    {
        return mb_strlen($value) <= $maxLength;
    }

    /**
     * Checks whether the value is equal or greater than the given min value.
     *
     * @param string $value
     * @param int    $min
     *
     * @return bool
     */
    public function min($value, $min)
    {
        return $value >= $min;
    }

    /**
     * Checks whether the value is equal or lower than the given max value.
     *
     * @param string $value
     * @param int    $max
     *
     * @return bool
     */
    public function max($value, $max)
    {
        return $value <= $max;
    }

    /**
     * Checks whether the value is an URL.
     *
     * @param string $value
     *
     * @return bool
     */
    public function url($value)
    {
        return $this->match($value, '@^\w+://(?:[\w-]+\.)*[\w-]+(?::\d+)?(?:/.*)?$@u');
    }

    /**
     * Checks whether the value is an email address.
     *
     * @param string $value
     *
     * @return bool
     */
    public function email($value)
    {
        return $this->match($value, '/^[\w.-]+@[\w.-]+\.[a-z]{2,}$/ui');
    }

    /**
     * Checks whether the value matches the given regex.
     *
     * @param string $value
     * @param string $regex
     *
     * @return bool
     */
    public function match($value, $regex)
    {
        return (bool) preg_match($regex, $value);
    }

    /**
     * Checks whether the value does not match the given regex.
     *
     * @param string $value
     * @param string $regex
     *
     * @return bool
     */
    public function notMatch($value, $regex)
    {
        return !$this->match($value, $regex);
    }

    /**
     * Checks whether the value is one of the given valid values.
     *
     * @param string $value
     * @param array  $validValues
     *
     * @return bool
     */
    public function values($value, array $validValues)
    {
        return in_array($value, $validValues);
    }

    /**
     * Checks the value by using the given callable.
     *
     * @param string   $value
     * @param callable $callback
     *
     * @return bool
     */
    public function custom($value, callable $callback)
    {
        return $callback($value);
    }
}
