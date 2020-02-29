<?php

/**
 * Validator class.
 *
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

    /**
     * @param string|null $version
     * @return bool
     */
    public static function isUnstable($version): bool
    {
        // see https://www.php.net/manual/en/function.version-compare.php
        foreach (['dev', 'alpha', 'a', 'beta', 'b', 'rc', 'pl'] as $unstable) {
            if (false !== stripos($version, $unstable)) {
                return true;
            }
        }
        return false;
    }
}
