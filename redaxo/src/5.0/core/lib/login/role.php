<?php

interface rex_user_role_interface
{
  public function hasPerm($perm);

  public function getComplexPerm($user, $key);

  static public function get($id);
}