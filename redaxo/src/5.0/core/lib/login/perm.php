<?php

abstract class rex_perm
{
  const
    GENERAL = 'general',
    OPTIONS = 'options',
    EXTRAS = 'extras';

  static private $perms = array();

  static public function register($perm, $group = self::GENERAL)
  {
    self::$perms[$group][] = $perm;
  }

  static public function getAll($group = self::GENERAL)
  {
    return isset(self::$perms[$group]) ? self::$perms[$group] : array();
  }
}