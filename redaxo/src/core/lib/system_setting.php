<?php

/**
 * This class can be used to add settings to the system settings page.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
abstract class rex_system_setting
{
    /**
     * Settings array.
     *
     * @var self[]
     */
    private static $settings = [];

    /**
     * Returns the key for the rex property.
     *
     * @return string
     */
    abstract public function getKey();

    /**
     * Returns the field.
     *
     * @return rex_form_element
     */
    abstract public function getField();

    /**
     * Returns if the given value is valid for this setting.
     *
     * @param mixed $value Value
     *
     * @return bool|string true or an error message
     */
    abstract public function isValid($value);

    /**
     * Casts the given value.
     *
     * @param string $value Value
     *
     * @return mixed
     */
    public function cast($value)
    {
        return $value;
    }

    /**
     * Registers a setting object.
     *
     * @param self $setting Setting object
     */
    public static function register(self $setting)
    {
        self::$settings[] = $setting;
    }

    /**
     * Returns all registered setting objects.
     *
     * @return self[]
     */
    public static function getAll()
    {
        return self::$settings;
    }
}
