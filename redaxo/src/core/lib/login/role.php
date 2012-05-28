<?php

/**
 * Interface for user roles
 *
 * @author gharlan
 */
interface rex_user_role_interface
{
  /**
   * Returns if the role has the given permission
   *
   * @param string $perm Perm key
   */
  public function hasPerm($perm);

  /**
   * Returns the complex perm
   *
   * @param rex_user $user User instance
   * @param string $key Complex perm key
   * @return rex_complex_perm Complex perm
   */
  public function getComplexPerm(rex_user $user, $key);

  /**
   * Returns the role for the given ID
   *
   * @param integer $id ID
   * @return rex_user_role_interface Role instance
   */
  static public function get($id);
}
