<?php

namespace Redaxo\Core\View;

use Redaxo\Core\Exception\RuntimeException;
use ReflectionClass;

use function count;
use function is_file;
use function rtrim;
use function sprintf;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * Resolves the view file for a class, honouring registered overrides.
 *
 * By default a class's view is the co-located `ClassName.view.php`. A project or addon can replace
 * the view of any class — without touching that class — by registering an override for its FQCN or
 * a whole namespace (typically in its boot code). Overrides are identity-based, not path-based: they
 * do not depend on where the original view lives. Overrides apply globally, so to restyle only the
 * frontend register them under `Core::isFrontend()`.
 *
 * Last registration wins, so addons can override core and the project can override both. Classes
 * without a matching override fall back to their co-located view.
 */
final class ViewResolver
{
    /**
     * Class overrides map a FQCN to a view file, namespace overrides map a namespace (with trailing
     * backslash) to a directory.
     *
     * @var list<array{target: string, path: string}>
     */
    private static array $overrides = [];

    /** @var array<class-string, string> */
    private static array $resolved = [];

    /**
     * Registers a view file to be used for the given class instead of its co-located default.
     *
     * @param class-string $class
     */
    public static function override(string $class, string $viewFile): void
    {
        self::$overrides[] = ['target' => $class, 'path' => $viewFile];
        self::$resolved = [];
    }

    /**
     * Registers a directory of view overrides for all classes in the given namespace (including sub-namespaces).
     *
     * The view of a class is looked up by its path relative to the namespace, e.g. with namespace
     * `Redaxo\Core\Form` the class `Redaxo\Core\Form\Widget\Input` resolves to `<directory>/Widget/Input.view.php`.
     * Classes without a view file in the directory are not affected.
     */
    public static function overrideNamespace(string $namespace, string $directory): void
    {
        self::$overrides[] = ['target' => rtrim($namespace, '\\') . '\\', 'path' => rtrim($directory, '/')];
        self::$resolved = [];
    }

    /** @param class-string $class */
    public static function resolve(string $class): string
    {
        return self::$resolved[$class] ??= self::findOverride($class) ?? self::locate($class);
    }

    /** @param class-string $class */
    private static function findOverride(string $class): ?string
    {
        for ($i = count(self::$overrides) - 1; $i >= 0; --$i) {
            ['target' => $target, 'path' => $path] = self::$overrides[$i];

            if ($class === $target) {
                return $path;
            }

            if (str_ends_with($target, '\\') && str_starts_with($class, $target)) {
                $file = $path . '/' . str_replace('\\', '/', substr($class, strlen($target))) . '.view.php';

                if (is_file($file)) {
                    return $file;
                }
            }
        }

        return null;
    }

    /** @param class-string $class */
    private static function locate(string $class): string
    {
        $classFile = new ReflectionClass($class)->getFileName();

        if (false === $classFile) {
            throw new RuntimeException(sprintf('Cannot resolve a view for "%s": the class has no source file.', $class));
        }

        return substr($classFile, 0, -4) . '.view.php';
    }

    /** @internal Resets the registry; intended for test isolation. */
    public static function reset(): void
    {
        self::$overrides = [];
        self::$resolved = [];
    }
}
