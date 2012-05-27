<?php

/**
 * Class for user roles
 *
 * @author gharlan
 */
class rex_user_role implements rex_user_role_interface
{
  /**
   * Permissions
   *
   * @var array
   */
  private $perms = array();

  /**
   * Complex perm params
   *
   * @var array
   */
  private $complexPermParams = array();

  /**
   * Cache for complex perm instances
   *
   * @var array
   */
  private $complexPerms = array();

  /**
   * Constructor
   *
   * @param array $params Params
   */
  private function __construct(array $params)
  {
    foreach (array(rex_perm::GENERAL, rex_perm::OPTIONS, rex_perm::EXTRAS) as $key) {
      $perms = $params[$key] ? explode('|', trim($params[$key], '|')) : array();
      $this->perms = array_merge($this->perms, $perms);
      unset($params[$key]);
    }
    $this->complexPermParams = $params;
  }

  /* (non-PHPdoc)
   * @see rex_user_role_interface::hasPerm()
   */
  public function hasPerm($perm)
  {
    return in_array($perm, $this->perms);
  }

  /* (non-PHPdoc)
  * @see rex_user_role_interface::getComplexPerm()
  */
  public function getComplexPerm(rex_user $user, $key)
  {
    if (isset($this->complexPerms[$key])) {
      return $this->complexPerms[$key];
    }
    $perms = array();
    if (isset($this->complexPermParams[$key])) {
      $perms = $this->complexPermParams[$key] == rex_complex_perm::ALL ? rex_complex_perm::ALL : explode('|', trim($this->complexPermParams[$key], '|'));
    }
    $this->complexPerms[$key] = rex_complex_perm::get($user, $key, $perms);
    return $this->complexPerms[$key];
  }

  /* (non-PHPdoc)
   * @see rex_user_role_interface::get()
   */
  static public function get($id)
  {
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT perms FROM ' . rex::getTablePrefix() . 'user_role WHERE id = ?', array($id));
    if ($sql->getRows() == 0) {
      return null;
    }
    return new self($sql->getArrayValue('perms'));
  }

  static public function removeOrReplaceItem($params)
  {
    $key = $params['key'];
    $item = '|' . $params['item'] . '|';
    $new = isset($params['new']) ? '|' . $params['new'] . '|' : '|';
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT id, perms FROM ' . rex::getTable('user_role'));
    $update = rex_sql::factory();
    $update->prepareQuery('UPDATE ' . rex::getTable('user_role') . ' SET perms = ? WHERE id = ?');
    foreach ($sql as $row) {
      $perms = json_decode($row->getValue('perms'), true);
      if (isset($perms[$key]) && strpos($perms[$key], $item) !== false) {
        $perms[$key] = str_replace($item, $new, $perms[$key]);
        $update->execute(array(json_encode($perms), $row->getValue('id')));
      }
    }
  }
}
