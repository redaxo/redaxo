<?php

/**
 * Class for users
 *
 * @author gharlan
 */
class rex_user
{
  /**
   * SQL instance
   *
   * @var rex_sql
   */
  protected $sql;

  /**
   * User role instance
   *
   * @var rex_user_role_interface
   */
  protected $role;

  /**
   * Class name for user roles
   *
   * @var string
   */
  static protected $roleClass;

  /**
   * Constructor
   *
   * @param rex_sql $sql
   */
  public function __construct(rex_sql $sql)
  {
    $this->sql = $sql;
  }

  /**
   * Returns the value for the given key
   *
   * @param string $key Key
   * @return string value
   */
  public function getValue($key)
  {
    return $this->sql->getValue($key);
  }

  /**
   * Returns the user login
   *
   * @return string Login
   */
  public function getUserLogin()
  {
    return $this->sql->getValue('login');
  }

  /**
   * Returns the name
   *
   * @return string Name
   */
  public function getName()
  {
    return $this->sql->getValue('name');
  }

  /**
   * Returns if the user is an admin
   *
   * @return boolean
   */
  public function isAdmin()
  {
    return (boolean) $this->sql->getValue('admin');
  }

  /**
   * Returns the language
   *
   * @return string Language
   */
  public function getLanguage()
  {
    return $this->sql->getValue('language');
  }

  /**
   * Returns the start page
   *
   * @return string Start page
   */
  public function getStartPage()
  {
    return $this->sql->getValue('startpage');
  }

  /**
   * Returns if the user has a role
   *
   * @return boolean
   */
  public function hasRole()
  {
    if (self::$roleClass && !is_object($this->role) && ($role = $this->sql->getValue('role'))) {
      $class = self::$roleClass;
      $this->role = $class::get($role);
    }
    return is_object($this->role);
  }

  /**
   * Returns if the user has the given permission
   *
   * @param string $perm Perm key
   * @return boolean
   */
  public function hasPerm($perm)
  {
    if ($this->isAdmin()) {
      return true;
    }
    $result = false;
    if (strpos($perm, '/') !== false) {
      list($complexPerm, $method) = explode('/', $perm, 2);
      $complexPerm = $this->getComplexPerm($complexPerm);
      return $complexPerm ? $complexPerm->$method() : false;
    }
    if ($this->hasRole()) {
      $result = $this->role->hasPerm($perm);
    }
    if (!$result && in_array($perm, array('isAdmin', 'admin', 'admin[]'))) {
      return $this->isAdmin();
    }
    return $result;
  }

  /**
   * Returns the complex perm for the user
   *
   * @param string $key Complex perm key
   * @return rex_complex_perm Complex perm
   */
  public function getComplexPerm($key)
  {
    if ($this->hasRole()) {
      return $this->role->getComplexPerm($this, $key);
    }
    return rex_complex_perm::get($this, $key);
  }

  /**
   * Sets the role class
   *
   * @param string $class Class name
   */
  static public function setRoleClass($class)
  {
    self::$roleClass = $class;
  }
}
