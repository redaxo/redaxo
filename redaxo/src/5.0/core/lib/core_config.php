<?php

/**
 * Class for handling core configurations.
 * The configuration is persisted between requests.
 *
 * @author gharlan
 */
class rex_core_config
{
  /**
   * Constant for the redaxo core namespace
   * @var string
   */
  const CORE_NS = 'rex-core';

  /**
   * @see rex_config::set()
   */
  public static function set($key, $value)
  {
    return rex_config::set(self::CORE_NS, $key, $value);
  }

  /**
   * @see rex_config::get()
   */
  public static function get($key, $default = null)
  {
    return rex_config::get(self::CORE_NS, $key, $default);
  }

/**
   * @see rex_config::has()
   */
  public static function has($key)
  {
    return rex_config::has(self::CORE_NS, $key);
  }

  /**
   * @see rex_config::remove()
   */
  public static function remove($key)
  {
    return rex_config::remove(self::CORE_NS, $key);
  }
}