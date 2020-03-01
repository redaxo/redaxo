<?php

/**
 * @author staabm
 *
 * @package redaxo\core
 */
class rex_version
{
    private function __construct()
    {
        // noop
    }

    public static function isUnstable(?string $version): bool
    {
        // see https://www.php.net/manual/en/function.version-compare.php
        return (bool) preg_match('/(?<![a-z])(?:dev|alpha|a|beta|b|rc|pl)(?![a-z])/i', $version);
    }
}
