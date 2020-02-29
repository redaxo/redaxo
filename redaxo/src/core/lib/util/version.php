<?php

class rex_version {
    /**
     * Constructor.
     */
    private function __construct()
    {
        // noop
    }

    static function isUnstable(string $version): bool {
        foreach(['dev', 'beta', 'rc'] as $unstable) {
            if(stripos($version, $unstable) !== false) {
                return true;
            }
        }
        return false;
    }
}
