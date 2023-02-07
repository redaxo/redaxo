<?php

/**
 * Class for permissions.
 *
 * @author gharlan
 *
 * @package redaxo\core\login
 */
abstract class rex_perm
{
    public const GENERAL = 'general';
    public const OPTIONS = 'options';
    public const EXTRAS = 'extras';

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
     * @return void
     */
    public static function register($perm, $name = null, $group = self::GENERAL)
    {
        if ($name) {
            $name = $perm.' :: '.$name;
        } else {
            $name = (rex_i18n::hasMsg($key = 'perm_'.$group.'_'.$perm) ? $perm.' :: '.rex_i18n::rawMsg($key) : $perm);
        }

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
            natcasesort($perms);
            return $perms;
        }
        return [];
    }
}
