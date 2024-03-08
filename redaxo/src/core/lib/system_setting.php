<?php

use Redaxo\Core\Form\Field\BaseField;

/**
 * This class can be used to add settings to the system settings page.
 */
abstract class rex_system_setting
{
    /**
     * Settings array.
     *
     * @var list<self>
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
     * @return BaseField
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
     * @return list<self>
     */
    public static function getAll()
    {
        return self::$settings;
    }
}
