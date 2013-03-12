<?php

/**
 * Class for user roles
 *
 * @author gharlan
 * @package redaxo\users
 */
class rex_user_role implements rex_user_role_interface
{
    /**
     * Permissions
     *
     * @var array
     */
    private $perms = [];

    /**
     * Complex perm params
     *
     * @var array
     */
    private $complexPermParams = [];

    /**
     * Cache for complex perm instances
     *
     * @var array
     */
    private $complexPerms = [];

    /**
     * Constructor
     *
     * @param array $params Params
     */
    private function __construct(array $params)
    {
        foreach ([rex_perm::GENERAL, rex_perm::OPTIONS, rex_perm::EXTRAS] as $key) {
            $perms = $params[$key] ? explode('|', trim($params[$key], '|')) : [];
            $this->perms = array_merge($this->perms, $perms);
            unset($params[$key]);
        }
        $this->complexPermParams = $params;
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
        $perms = [];
        if (isset($this->complexPermParams[$key])) {
            $perms = $this->complexPermParams[$key] == rex_complex_perm::ALL ? rex_complex_perm::ALL : explode('|', trim($this->complexPermParams[$key], '|'));
        }
        $this->complexPerms[$key] = rex_complex_perm::get($user, $key, $perms);
        return $this->complexPerms[$key];
    }

    /**
     * {@inheritdoc}
     */
    public static function get($id)
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT perms FROM ' . rex::getTablePrefix() . 'user_role WHERE id = ?', [$id]);
        if ($sql->getRows() == 0) {
            return null;
        }
        return new self($sql->getArrayValue('perms'));
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
