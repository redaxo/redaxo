<?php

/**
 * Klasse die Einsprungpunkte zur Erweiterung der Kernfunktionalitaetet bietet.
 *
 * @author Markus Staab
 *
 * @package redaxo\core
 */
abstract class rex_extension
{
    use rex_factory_trait;

    public const EARLY = -1;
    public const NORMAL = 0;
    public const LATE = 1;

    /**
     * Array of registered extensions.
     *
     * @var array
     */
    private static $extensions = [];

    /**
     * Registers an extension point.
     *
     * @param rex_extension_point $extensionPoint Extension point
     *
     * @return mixed Subject, maybe adjusted by the extensions
     */
    public static function registerPoint(rex_extension_point $extensionPoint)
    {
        if (static::hasFactoryClass()) {
            return static::callFactoryClass(__FUNCTION__, func_get_args());
        }

        $name = $extensionPoint->getName();

        foreach ([self::EARLY, self::NORMAL, self::LATE] as $level) {
            if (isset(self::$extensions[$name][$level]) && is_array(self::$extensions[$name][$level])) {
                foreach (self::$extensions[$name][$level] as $extensionAndParams) {
                    [$extension, $params] = $extensionAndParams;
                    $extensionPoint->setExtensionParams($params);
                    $subject = call_user_func($extension, $extensionPoint);
                    // Update subject only if the EP is not readonly and the extension has returned something
                    if (!$extensionPoint->isReadonly() && null !== $subject) {
                        $extensionPoint->setSubject($subject);
                    }
                }
            }
        }

        return $extensionPoint->getSubject();
    }

    /**
     * Registers an extension for an extension point.
     *
     * @param string|string[] $extensionPoint Name(s) of extension point(s)
     * @param callable        $extension      Callback extension
     * @param int             $level          Runlevel (`rex_extension::EARLY`, `rex_extension::NORMAL` or `rex_extension::LATE`)
     * @param array           $params         Additional params
     */
    public static function register($extensionPoint, callable $extension, $level = self::NORMAL, array $params = [])
    {
        if (static::hasFactoryClass()) {
            static::callFactoryClass(__FUNCTION__, func_get_args());
            return;
        }
        foreach ((array) $extensionPoint as $ep) {
            self::$extensions[$ep][(int) $level][] = [$extension, $params];
        }
    }

    /**
     * Checks whether an extension is registered for the given extension point.
     *
     * @param string $extensionPoint Name of extension point
     *
     * @return bool
     */
    public static function isRegistered($extensionPoint)
    {
        if (static::hasFactoryClass()) {
            return static::callFactoryClass(__FUNCTION__, func_get_args());
        }
        return !empty(self::$extensions[$extensionPoint]);
    }
}
