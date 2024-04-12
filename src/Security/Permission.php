<?php

namespace Redaxo\Core\Security;

use Redaxo\Core\Translation\I18n;

abstract class Permission
{
    public const GENERAL = 'general';
    public const OPTIONS = 'options';
    public const EXTRAS = 'extras';

    /**
     * Array of permissions.
     *
     * @var array<self::*, array<string, string>>
     */
    private static array $perms = [];

    /**
     * Registers a new permission.
     *
     * @param string $perm Perm key
     * @param string|null $name Perm name
     * @param self::* $group Perm group, possible values are Permission::GENERAL, Permission::OPTIONS and Permission::EXTRAS
     * @return void
     */
    public static function register($perm, $name = null, $group = self::GENERAL)
    {
        if ($name) {
            $name = $perm . ' :: ' . $name;
        } else {
            $name = (I18n::hasMsg($key = 'perm_' . $group . '_' . $perm) ? $perm . ' :: ' . I18n::rawMsg($key) : $perm);
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
     * @param self::* $group Perm group
     *
     * @return array<string, string> Permissions
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
