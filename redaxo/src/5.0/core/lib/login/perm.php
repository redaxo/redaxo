<?php

abstract class rex_perm
{
  const
    GENERAL = 'general',
    OPTIONS = 'options',
    EXTRAS = 'extras';

  static private $perms = array();

  static public function register($perm, $name = null, $group = self::GENERAL)
  {
    $name = $name ?: (rex_i18n::hasMsg($key = 'perm_'. $group .'_'. $perm) ? rex_i18n::msg($key) : $perm);
    self::$perms[$group][$perm] = $name;
  }

  static public function getAll($group = self::GENERAL)
  {
    return isset(self::$perms[$group]) ? self::$perms[$group] : array();
  }
}