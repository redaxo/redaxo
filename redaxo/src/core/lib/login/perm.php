<?php

/**
 * Class for permissions.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
abstract class rex_perm
{
    const GENERAL = 'general';
    const OPTIONS = 'options';
    const EXTRAS = 'extras';

    /**
     * Array of permissions.
     *
     * @var array
     */
    private static $perms = [];

    /**
     * Registers a new permission.
     *
     * @param string $perm  Perm key
     * @param string $name  Perm name
     * @param string $group Perm group, possible values are rex_perm::GENERAL, rex_perm::OPTIONS and rex_perm::EXTRAS
     */
    public static function register($perm, $name = null, $group = self::GENERAL)
    {
        $name = $name ?: (rex_i18n::hasMsg($key = 'perm_' . $group . '_' . $perm) ? rex_i18n::msg($key) : $perm);
        self::$perms[$group][$perm] = $name;
    }

    /**
     * Returns whether the permission is registered.
     *
     * @param string $perm
     *
     * @return bool
     */
    public static function has($perm)
    {
        foreach (self::$perms as $perms) {
            if (isset($perms[$perm])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns all permissions for the given group.
     *
     * @param string $group Perm group
     *
     * @return array Permissions
     */
    public static function getAll($group = self::GENERAL)
    {
        if (isset(self::$perms[$group])) {
            $perms = self::$perms[$group];
            sort($perms);
            return $perms;
        }
        return [];
    }
}
