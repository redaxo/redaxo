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
     * Returns the setting key.
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
     * Sets the new value.
     *
     * @param mixed $value
     *
     * @return string|bool True, when everything went well. String a errormessage in case of failure.
     */
    abstract public function setValue($value);

    /**
     * Registers a setting object.
     *
     * @param self $setting Setting object
     * @return void
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
