<?php

namespace Redaxo\Core\Backend;

/** Accesskeys of the backend, keyboard shortcuts for the most common actions. */
final class Accesskey
{
    public static bool $enabled = true;

    /** @var array<string, string> */
    public static array $keys = [
        'save' => 's',
        'apply' => 'x',
        'delete' => 'd',
        'add' => 'a',
        'add_2' => 'y',
    ];

    private function __construct() {}

    /** Returns the accesskey for the given key, or `null` if accesskeys are disabled or the key has none. */
    public static function get(string $key): ?string
    {
        return self::$enabled ? self::$keys[$key] ?? null : null;
    }

    /**
     * Returns the title attribute for an element, including the accesskey attribute if the given key has one.
     *
     * @return non-empty-string
     */
    public static function attributes(string $title, string $key): string
    {
        $accesskey = self::get($key);

        if (null !== $accesskey) {
            return ' accesskey="' . $accesskey . '" title="' . $title . ' [' . $accesskey . ']"';
        }

        return ' title="' . $title . '"';
    }
}
