<?php

namespace Redaxo\Core\Cronjob;

use InvalidArgumentException;
use LogicException;
use Redaxo\Core\AbstractProject;
use Redaxo\Core\Addon\Addon;
use Redaxo\Core\ClassDiscovery;
use ReflectionMethod;

use function in_array;
use function sprintf;

/**
 * Registry of all cronjobs registered via {@see AsCronjob}.
 *
 * @internal
 */
final class CronjobRegistry
{
    /** @var array<non-empty-string, Cronjob>|null */
    private static ?array $cronjobs = null;

    /** @return array<non-empty-string, Cronjob> Map of name to cronjob, sorted by name */
    public static function all(): array
    {
        if (null !== self::$cronjobs) {
            return self::$cronjobs;
        }

        $entries = [];
        foreach (ClassDiscovery::getInstance()->discoverByMethodAttribute(AsCronjob::class) as $entry) {
            $entries[] = [
                'class' => $entry['class'],
                'method' => $entry['method'],
                'isStatic' => $entry['isStatic'],
                'attribute' => $entry['attribute'],
            ];
        }

        return self::$cronjobs = self::build($entries);
    }

    public static function get(string $name): ?Cronjob
    {
        return self::all()[$name] ?? null;
    }

    /**
     * @param list<array{class: class-string, method: string, isStatic: bool, attribute: AsCronjob}> $entries
     * @return array<non-empty-string, Cronjob>
     */
    public static function build(array $entries): array
    {
        $cronjobs = [];

        foreach ($entries as $entry) {
            $name = $entry['attribute']->name;
            $callable = $entry['class'] . '::' . $entry['method'] . '()';

            // the name is listed in the comma-separated `REX_CRONJOBS`, where a leading `-` excludes it
            if (!preg_match('/^[\w.:][\w.:-]*$/', $name) || in_array($name, ['all', 'none'], true)) {
                throw new LogicException(sprintf('The cronjob name "%s" (%s) is invalid: it may only contain letters, digits, "_", ".", ":" and "-" (not at the beginning), and must not be "all" or "none".', $name, $callable));
            }

            if (isset($cronjobs[$name])) {
                $existing = $cronjobs[$name];
                throw new LogicException(sprintf('The cronjob name "%s" is used by both "%s::%s()" and "%s".', $name, $existing->class, $existing->method, $callable));
            }

            $method = new ReflectionMethod($entry['class'], $entry['method']);
            if (!$method->isPublic() || $method->getNumberOfRequiredParameters() > 0) {
                throw new LogicException(sprintf('The cronjob method "%s" must be public and must not require parameters.', $callable));
            }

            if (!$entry['isStatic'] && !is_a($entry['class'], AbstractProject::class, true) && !is_a($entry['class'], Addon::class, true)) {
                throw new LogicException(sprintf('The cronjob method "%s" must be static, non-static methods are only supported on the project and addon classes.', $callable));
            }

            try {
                $cronjobs[$name] = new Cronjob($name, $entry['attribute']->schedule, $entry['class'], $entry['method'], $entry['isStatic']);
            } catch (InvalidArgumentException $exception) {
                throw new LogicException(sprintf('The cronjob "%s" (%s) has an invalid schedule: %s', $name, $callable, $exception->getMessage()), previous: $exception);
            }
        }

        ksort($cronjobs);

        return $cronjobs;
    }

    /** @internal Reset the discovery cache (mainly for tests). */
    public static function reset(): void
    {
        self::$cronjobs = null;
    }
}
