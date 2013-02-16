<?php

/**
 * Abstract class for complex permissions
 *
 * All permission check methods ("hasPerm()" etc.) in child classes should return "true" for admins
 *
 * @author gharlan
 */
abstract class rex_complex_perm
{
  const ALL = 'all';

  /**
   * User instance
   *
   * @var rex_user
   */
  protected $user;

  /**
   * Array of permissions
   *
   * @var array
   */
  protected $perms = array();

  /**
   * Array of class names
   *
   * @var array
   */
  static private $classes = array();

  /**
   * Constructor
   *
   * @param rex_user $user  User instance
   * @param mixed    $perms Permissions
   */
  protected function __construct(rex_user $user, $perms)
  {
    $this->user = $user;
    $this->perms = $perms;
  }

  /**
   * Returns if the user has the permission for all items
   *
   * @return boolean
   */
  public function hasAll()
  {
    return $this->user->isAdmin() || $this->perms == self::ALL;
  }

  /**
   * Returns the field params for the role form
   *
   * @return array
   */
  static public function getFieldParams()
  {
    return array();
  }

  /**
   * Registers a new complex perm class
   *
   * @param string $key   Key for the complex perm
   * @param string $class Class name
   * @throws rex_exception
   */
  static public function register($key, $class)
  {
    if (!is_subclass_of($class, __CLASS__)) {
      throw new rex_exception(sprintf('$class must be a subclass of %s!', __CLASS__));
    }
    self::$classes[$key] = $class;
  }

  /**
   * Returns all complex perm classes
   *
   * @return array Class names
   */
  static public function getAll()
  {
    return self::$classes;
  }

  /**
   * Returns the complex perm
   *
   * @param rex_user $user  User instance
   * @param string   $key   Complex perm key
   * @param mixed    $perms Permissions
   * @return self
   */
  static public function get(rex_user $user, $key, $perms = array())
  {
    if (!isset(self::$classes[$key])) {
      return null;
    }
    $class = self::$classes[$key];
    return new $class($user, $perms);
  }

  /**
   * Should be called if an item is removed
   *
   * @param string $key  Key
   * @param string $item Item
   */
  static public function removeItem($key, $item)
  {
    rex_extension::registerPoint('COMPLEX_PERM_REMOVE_ITEM', '', array('key' => $key, 'item' => $item), true);
  }



  /**
   * Should be called if an item is replaced
   *
   * @param string $key  Key
   * @param string $item Old item
   * @param string $new  New item
   */
  static public function replaceItem($key, $item, $new)
  {
    rex_extension::registerPoint('COMPLEX_PERM_REPLACE_ITEM', '', array('key' => $key, 'item' => $item, 'new' => $new), true);
  }
}
