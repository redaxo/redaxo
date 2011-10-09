<?php

/**
 * This class can be used to add settings to the system settings page
 *
 * @author gharlan
 */
abstract class rex_system_setting
{
  /**
   * Settings array
   *
   * @var array[rex_system_setting]
   */
  static private $settings = array();

  /**
   * Returns the key for the rex property
   *
   * @return string
   */
  abstract public function getKey();

  /**
   * Returns the id attribute
   *
   * @return string
   */
  public function getId()
  {
    return 'rex-form-'. str_replace('_', '-', $this->getKey());
  }

  /**
   * Returns the name attribute
   *
   * @return string
   */
  final public function getName()
  {
    return 'settings['. $this->getKey() .']';
  }

  /**
   * Returns the class attribute
   *
   * @return string
   */
  abstract public function getClass();

  /**
  * Returns the label
  *
  * @return string
  */
  abstract public function getLabel();

  /**
  * Returns the field
  *
  * @return string
  */
  abstract public function getField();

  /**
  * Returns if the given value is valid for this setting
  *
  * @param mixed $value Value
  * @return booleand|string true or an error message
  */
  abstract public function isValid($value);

  /**
  * Casts the given value
  *
  * @param string $value Value
  * @return mixed
  */
  public function cast($value)
  {
    return $value;
  }

  /**
  * Registers a setting object
  *
  * @param rex_system_setting $setting Setting object
  */
  static public function register(self $setting)
  {
    self::$settings[] = $setting;
  }

  /**
  * Returns all registered setting objects
  *
  * @return array[rex_system_setting]
  */
  static public function getAll()
  {
    return self::$settings;
  }
}