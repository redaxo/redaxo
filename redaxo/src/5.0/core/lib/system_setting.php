<?php

abstract class rex_system_setting
{
  static private $settings = array();

  abstract public function getKey();

  public function getId()
  {
    return 'rex-form-'. str_replace('_', '-', $this->getKey());
  }

  final public function getName()
  {
    return 'settings['. $this->getKey() .']';
  }

  abstract public function getClass();

  abstract public function getLabel();

  abstract public function getField();

  abstract public function isValid($value);

  public function cast($value)
  {
    return $value;
  }

  static public function register(self $setting)
  {
    self::$settings[] = $setting;
  }

  static public function getAll()
  {
    return self::$settings;
  }
}