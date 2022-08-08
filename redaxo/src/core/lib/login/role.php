<?php

/**
 * Interface for user roles.
 *
 * @author gharlan
 *
 * @package redaxo\core\login
 */
interface rex_user_role_interface
{
    /**
     * Returns if the role has the given permission.
     *
     * @param string $perm Perm key
     * @return bool
     */
    public function hasPerm($perm);

    /**
     * Returns the complex perm.
     *
     * @param rex_user $user User instance
     * @param string   $key  Complex perm key
     *
     * @return rex_complex_perm|null Complex perm
     */
    public function getComplexPerm(rex_user $user, $key);

    /**
     * Returns the role for the given ID.
     *
     * @param string $id IDs comma seperated
     *
     * @return null|static Role instance
     */
    public static function get($id);
}
