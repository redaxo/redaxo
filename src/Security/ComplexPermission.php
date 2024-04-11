<?php

namespace Redaxo\Core\Security;

use InvalidArgumentException;
use rex_extension;
use rex_extension_point;

/**
 * Abstract class for complex permissions.
 *
 * All permission check methods ("hasPerm()" etc.) in child classes should return "true" for admins
 */
abstract class ComplexPermission
{
    public const ALL = 'all';

    /**
     * User instance.
     *
     * @var User
     */
    protected $user;

    /**
     * Array of permissions.
     *
     * @var array
     */
    protected $perms = [];

    /**
     * Array of class names.
     *
     * @var array<string, class-string<\Redaxo\Core\Security\ComplexPermission>>
     */
    private static $classes = [];

    /**
     * @param User $user User instance
     * @param mixed $perms Permissions
     */
    protected function __construct(User $user, $perms)
    {
        $this->user = $user;
        $this->perms = $perms;
    }

    /**
     * Returns if the user has the permission for all items.
     *
     * @return bool
     */
    public function hasAll()
    {
        return $this->user->isAdmin() || self::ALL == $this->perms;
    }

    /**
     * Returns the field params for the role form.
     *
     * @return array
     */
    public static function getFieldParams()
    {
        return [];
    }

    /**
     * Registers a new complex perm class.
     *
     * @param string $key Key for the complex perm
     * @param class-string<self> $class Class name
     * @throws InvalidArgumentException
     * @return void
     */
    public static function register($key, $class)
    {
        if (!is_subclass_of($class, self::class)) {
            throw new InvalidArgumentException(sprintf('Class "%s" must be a subclass of "%s"!', $class, self::class));
        }
        self::$classes[$key] = $class;
    }

    /**
     * Returns all complex perm classes.
     *
     * @return array<string, class-string<\Redaxo\Core\Security\ComplexPermission>> Class names
     */
    public static function getAll()
    {
        return self::$classes;
    }

    /**
     * Returns the complex perm.
     *
     * @param User $user User instance
     * @param string $key Complex perm key
     * @param mixed $perms Permissions
     *
     * @return ComplexPermission|null
     */
    public static function get(User $user, $key, $perms = [])
    {
        if (!isset(self::$classes[$key])) {
            return null;
        }
        $class = self::$classes[$key];
        return new $class($user, $perms);
    }

    /**
     * Should be called if an item is removed.
     *
     * @param string $key Key
     * @param string|int $item Item
     * @return void
     */
    public static function removeItem($key, $item)
    {
        rex_extension::registerPoint(new rex_extension_point('COMPLEX_PERM_REMOVE_ITEM', '', ['key' => $key, 'item' => $item], true));
    }

    /**
     * Should be called if an item is replaced.
     *
     * @param string $key Key
     * @param string|int $item Old item
     * @param string|int $new New item
     * @return void
     */
    public static function replaceItem($key, $item, $new)
    {
        rex_extension::registerPoint(new rex_extension_point('COMPLEX_PERM_REPLACE_ITEM', '', ['key' => $key, 'item' => $item, 'new' => $new], true));
    }
}
