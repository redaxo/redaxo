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
     * @var array<string, array<self::*, list<array{callable, array}>>>
     */
    private static $extensions = [];

    /**
     * Registers an extension point.
     *
     * @param rex_extension_point $extensionPoint Extension point
     *
     * @return mixed Subject, maybe adjusted by the extensions
     *
     * @psalm-taint-specialize
     */
    public static function registerPoint(rex_extension_point $extensionPoint)
    {
        if (static::hasFactoryClass()) {
            return static::callFactoryClass(__FUNCTION__, func_get_args());
        }

        $name = $extensionPoint->getName();

        rex_timer::measure('EP: '.$name, static function () use ($extensionPoint, $name) {
            foreach ([self::EARLY, self::NORMAL, self::LATE] as $level) {
                if (!isset(self::$extensions[$name][$level]) || !is_array(self::$extensions[$name][$level])) {
                    continue;
                }

                foreach (self::$extensions[$name][$level] as $extensionAndParams) {
                    [$extension, $params] = $extensionAndParams;
                    $extensionPoint->setExtensionParams($params);
                    $subject = call_user_func($extension, $extensionPoint);
                    // Update subject only if the EP is not readonly and the extension has returned something
                    if ($extensionPoint->isReadonly()) {
                        continue;
                    }
                    if (null === $subject) {
                        continue;
                    }
                    $extensionPoint->setSubject($subject);
                }
            }
        });

        return $extensionPoint->getSubject();
    }

    /**
     * Registers an extension for an extension point.
     *
     * @param string|string[] $extensionPoint Name(s) of extension point(s)
     * @param callable        $extension      Callback extension
     * @param self::*         $level          Runlevel (`rex_extension::EARLY`, `rex_extension::NORMAL` or `rex_extension::LATE`)
     * @param array           $params         Additional params
     *
     * @template T as rex_extension_point
     * @psalm-param callable(T):mixed $extension
     */
    public static function register($extensionPoint, callable $extension, $level = self::NORMAL, array $params = [])
    {
        if (static::hasFactoryClass()) {
            static::callFactoryClass(__FUNCTION__, func_get_args());
            return;
        }

        // bc
        if (is_string($level)) {
            trigger_error(__METHOD__.': Argument $level should be one of the constants rex_extension::EARLY/NORMAL/LATE, but string "'.$level.'" given', E_USER_WARNING);

            $level = (int) $level;
        }

        if (!in_array($level, [self::EARLY, self::NORMAL, self::LATE], true)) {
            throw new InvalidArgumentException('Argument $level should be one of the constants rex_extension::EARLY/NORMAL/LATE, but "'.(is_int($level) ? $level : get_debug_type($level)).'" given');
        }

        foreach ((array) $extensionPoint as $ep) {
            self::$extensions[$ep][$level][] = [$extension, $params];
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
