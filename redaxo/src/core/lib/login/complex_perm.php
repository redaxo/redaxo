<?php

/**
 * Abstract class for complex permissions.
 *
 * All permission check methods ("hasPerm()" etc.) in child classes should return "true" for admins
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
abstract class rex_complex_perm
{
    const ALL = 'all';

    /**
     * User instance.
     *
     * @var rex_user
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
     * @var array
     */
    private static $classes = [];

    /**
     * Constructor.
     *
     * @param rex_user $user  User instance
     * @param mixed    $perms Permissions
     */
    protected function __construct(rex_user $user, $perms)
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
        return $this->user->isAdmin() || $this->perms == self::ALL;
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
     * @param string $key   Key for the complex perm
     * @param string $class Class name
     *
     * @throws InvalidArgumentException
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
     * @return array Class names
     */
    public static function getAll()
    {
        return self::$classes;
    }

    /**
     * Returns the complex perm.
     *
     * @param rex_user $user  User instance
     * @param string   $key   Complex perm key
     * @param mixed    $perms Permissions
     *
     * @return self
     */
    public static function get(rex_user $user, $key, $perms = [])
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
     * @param string $key  Key
     * @param string $item Item
     */
    public static function removeItem($key, $item)
    {
        rex_extension::registerPoint(new rex_extension_point('COMPLEX_PERM_REMOVE_ITEM', '', ['key' => $key, 'item' => $item], true));
    }

    /**
     * Should be called if an item is replaced.
     *
     * @param string $key  Key
     * @param string $item Old item
     * @param string $new  New item
     */
    public static function replaceItem($key, $item, $new)
    {
        rex_extension::registerPoint(new rex_extension_point('COMPLEX_PERM_REPLACE_ITEM', '', ['key' => $key, 'item' => $item, 'new' => $new], true));
    }
}
