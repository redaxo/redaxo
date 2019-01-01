<?php

/**
 * @author gharlan
 *
 * @package redaxo\core\login
 */
class rex_backend_password_policy extends rex_password_policy
{
    use rex_factory_trait;

    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    /**
     * @return static
     */
    public static function factory(array $options)
    {
        $class = static::getFactoryClass();

        return new $class($options);
    }
}
