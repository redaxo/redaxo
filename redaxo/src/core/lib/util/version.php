<?php

class rex_version
{
    /**
     * Constructor.
     */
    private function __construct()
    {
        // noop
    }

    public static function isUnstable(string $version): bool
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
