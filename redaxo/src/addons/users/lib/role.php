<?php

/**
 * Class for user roles.
 *
 * @author gharlan
 *
 * @package redaxo\users
 */
class rex_user_role implements rex_user_role_interface
{
    /**
     * Permissions.
     *
     * @var array
     */
    private $perms = [];

    /**
     * Complex perm params.
     *
     * @var array
     */
    private $complexPermParams = [];

    /**
     * Cache for complex perm instances.
     *
     * @var array
     */
    private $complexPerms = [];

    /**
     * Constructor.
     *
     * @param array $roles
     */
    private function __construct(array $roles)
    {
        foreach ($roles as $role) {
            foreach ([rex_perm::GENERAL, rex_perm::OPTIONS, rex_perm::EXTRAS] as $key) {
                $perms = $role[$key] ? explode('|', trim($role[$key], '|')) : [];
                $this->perms = array_merge($this->perms, $perms);
                unset($role[$key]);
            }

            foreach ($role as $key => $value) {
                if (rex_complex_perm::ALL === $role[$key]) {
                    $perms = rex_complex_perm::ALL;
                } else {
                    $perms = explode('|', trim($role[$key], '|'));
                    if (count($perms) == 1 && $perms[0] == '') {
                        $perms = [];
                    }
                }

                if (!isset($this->complexPermParams[$key])) {
                    $this->complexPermParams[$key] = $perms;
                } elseif ($this->complexPermParams[$key] == rex_complex_perm::ALL) {
                } elseif ($perms == rex_complex_perm::ALL) {
                    $this->complexPermParams[$key] = $perms;
                } else {
                    $this->complexPermParams[$key] = array_merge($perms, $this->complexPermParams[$key]);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasPerm($perm)
    {
        return in_array($perm, $this->perms);
    }

    /**
     * {@inheritdoc}
     */
    public function getComplexPerm(rex_user $user, $key)
    {
        if (isset($this->complexPerms[$key])) {
            return $this->complexPerms[$key];
        }
        if (!isset($this->complexPermParams[$key])) {
            $this->complexPermParams[$key] = [];
        }
        $this->complexPerms[$key] = rex_complex_perm::get($user, $key, $this->complexPermParams[$key]);
        return $this->complexPerms[$key];
    }

    /**
     * {@inheritdoc}
     */
    public static function get($ids)
    {
        $sql = rex_sql::factory();
        $user_roles = $sql->getArray('SELECT perms FROM ' . rex::getTablePrefix() . 'user_role WHERE FIND_IN_SET(id, ?)', [$ids]);
        if (count($user_roles) == 0) {
            return null;
        }

        $roles = [];
        foreach ($user_roles as $user_role) {
            $roles[] = json_decode($user_role['perms'], true);
        }

        return new self($roles);
    }

    public static function removeOrReplaceItem(rex_extension_point $ep)
    {
        $params = $ep->getParams();
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
                $update->execute([json_encode($perms), $row->getValue('id')]);
            }
        }
    }
}
