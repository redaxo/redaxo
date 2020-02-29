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
        foreach (['dev', 'beta', 'rc'] as $unstable) {
            if (false !== stripos($version, $unstable)) {
                return true;
            }
        }
        return false;
    }
}
