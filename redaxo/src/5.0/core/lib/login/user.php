<?php

class rex_user
{
  /**
   * @var rex_sql
   */
  protected $sql;

  /**
   * @var rex_user_role_interface
   */
  protected $role;

  static protected $roleClass;

  public function __construct(rex_sql $sql)
  {
    $this->sql = $sql;
  }

  public function getValue($key)
  {
    return $this->sql->getValue($key);
  }

  public function getUserLogin()
  {
    return $this->sql->getValue('login');
  }

  public function getName()
  {
    return $this->sql->getValue('name');
  }

  public function isAdmin()
  {
    return (boolean) $this->sql->getValue('admin');
  }

  public function getLanguage()
  {
    return $this->sql->getValue('language');
  }

  public function getStartPage()
  {
    return $this->sql->getValue('startpage');
  }

  public function hasRole()
  {
    if(self::$roleClass && !is_object($this->role) && ($role = $this->sql->getValue('role')))
    {
      $class = self::$roleClass;
      $this->role = $class::get($role);
    }
    return is_object($this->role);
  }

  public function hasPerm($perm)
  {
    $result = false;
    if(strpos($perm, '/') !== false)
    {
      list($complexPerm, $method) = explode('/', $perm, 2);
      $complexPerm = $this->getComplexPerm($complexPerm);
      return $complexPerm ? $complexPerm->$method() : false;
    }
    if($this->hasRole())
    {
      $result = $this->role->hasPerm($perm);
    }
    if(!$result && in_array($perm, array('isAdmin', 'admin', 'admin[]')))
    {
      return $this->isAdmin();
    }
    return $result;
  }

  public function getComplexPerm($key)
  {
    if($this->hasRole())
    {
      return $this->role->getComplexPerm($this, $key);
    }
    return rex_complex_perm::get($this, $key);
  }

  static public function setRoleClass($class)
  {
    self::$roleClass = $class;
  }
}