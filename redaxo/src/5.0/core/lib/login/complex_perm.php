<?php

abstract class rex_complex_perm
{
  const ALL = 'all';

  /**
	 * @var rex_user
   */
  protected $user;

  protected $perms = array();

  static private $classes = array();

  protected function __construct($user, $perms)
  {
    $this->user = $user;
    $this->perms = $perms;
  }

  protected function hasAll()
  {
    return $this->user->isAdmin() || $this->perms == self::ALL;
  }

  static public function getFieldParams()
  {
    return array();
  }

  static public function register($key, $class)
  {
    if(!is_subclass_of($class, __CLASS__))
    {
      throw new rex_exception(sprintf('$class must be a subclass of %s!', __CLASS__));
    }
    self::$classes[$key] = $class;
  }

  static public function getAll()
  {
    return self::$classes;
  }

  static public function get($user, $key, $perms = array())
  {
    if(!isset(self::$classes[$key]))
    {
      return null;
    }
    $class = self::$classes[$key];
    return new $class($user, $perms);
  }

  static public function removeItem($key, $item)
  {
    rex_extension::registerPoint('COMPLEX_PERM_REMOVE_ITEM', '', array('key' => $key, 'item' => $item), true);
  }

  static public function replaceItem($key, $item, $new)
  {
    rex_extension::registerPoint('COMPLEX_PERM_REPLACE_ITEM', '', array('key' => $key, 'item' => $item, 'new' => $new), true);
  }
}