<?php

namespace Redaxo\Core\Security;

interface UserRoleInterface
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
     * @param User $user User instance
     * @param string $key Complex perm key
     *
     * @return ComplexPermission|null Complex perm
     */
    public function getComplexPerm(User $user, $key);

    /**
     * Returns the role for the given ID.
     *
     * @param string $id IDs comma seperated
     *
     * @return static|null Role instance
     */
    public static function get($id);
}
