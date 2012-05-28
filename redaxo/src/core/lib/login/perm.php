<?php

/**
 * Class for permissions
 *
 * @author gharlan
 */
abstract class rex_perm
{
  const
    GENERAL = 'general',
    OPTIONS = 'options',
    EXTRAS = 'extras';

  /**
   * Array of permissions
   *
   * @var array
   */
  static private $perms = array();

  /**
   * Registers a new permission
   *
   * @param string $perm Perm key
   * @param string $name Perm name
   * @param string $group Perm group, possible values are rex_perm::GENERAL, rex_perm::OPTIONS and rex_perm::EXTRAS
   */
  static public function register($perm, $name = null, $group = self::GENERAL)
  {
    $name = $name ?: (rex_i18n::hasMsg($key = 'perm_'. $group .'_'. $perm) ? rex_i18n::msg($key) : $perm);
    self::$perms[$group][$perm] = $name;
  }

  /**
   * Returns all permissions for the given group
   *
   * @param string $group Perm group
   * @return array Permissions
   */
  static public function getAll($group = self::GENERAL)
  {
    return isset(self::$perms[$group]) ? self::$perms[$group] : array();
  }
}
