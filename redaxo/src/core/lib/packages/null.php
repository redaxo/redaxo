<?php

use Redaxo\Core\Translation\I18n;

/**
 * Represents a null addon.
 *
 * Instances of this class are returned by `rex_addon::get()` for non-existing addons.
 * Thereby it is safe to call `rex_addon::get(...)->isAvailable()` and `isInstalled()`.
 * Other methods should not be called on null-addons since they do not return useful values.
 * Some methods like `getPath()` throw exceptions.
 */
class rex_null_addon implements rex_addon_interface
{
    use rex_singleton_trait;

    public function getName()
    {
        return static::class;
    }

    /**
     * @return rex_null_addon
     */
    public function getAddon()
    {
        return self::getInstance();
    }

    public function getPackageId()
    {
        return null;
    }

    public function getType()
    {
        return 'addon';
    }

    public function getPath($file = '')
    {
        throw new rex_exception(sprintf('Calling %s on %s is not allowed', __FUNCTION__, self::class));
    }

    public function getAssetsPath($file = '')
    {
        throw new rex_exception(sprintf('Calling %s on %s is not allowed', __FUNCTION__, self::class));
    }

    public function getAssetsUrl($file = '')
    {
        throw new rex_exception(sprintf('Calling %s on %s is not allowed', __FUNCTION__, self::class));
    }

    public function getDataPath($file = '')
    {
        throw new rex_exception(sprintf('Calling %s on %s is not allowed', __FUNCTION__, self::class));
    }

    public function getCachePath($file = '')
    {
        throw new rex_exception(sprintf('Calling %s on %s is not allowed', __FUNCTION__, self::class));
    }

    public function setConfig(string|array $key, mixed $value = null): bool
    {
        return false;
    }

    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        return $default;
    }

    public function hasConfig(?string $key = null): bool
    {
        return false;
    }

    public function removeConfig(string $key): bool
    {
        return false;
    }

    public function setProperty(string $key, mixed $value): void {}

    public function getProperty(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    public function hasProperty(string $key): bool
    {
        return false;
    }

    public function removeProperty(string $key): void {}

    public function isAvailable()
    {
        return false;
    }

    public function isInstalled()
    {
        return false;
    }

    public function isSystemPackage()
    {
        return false;
    }

    public function getAuthor($default = null)
    {
        return $default;
    }

    public function getVersion($format = null)
    {
        return '';
    }

    public function getSupportPage($default = null)
    {
        return $default;
    }

    public function includeFile($file, array $context = [])
    {
        return null;
    }

    public function i18n($key, ...$replacements)
    {
        $args = func_get_args();
        return call_user_func_array(I18n::msg(...), $args);
    }
}
