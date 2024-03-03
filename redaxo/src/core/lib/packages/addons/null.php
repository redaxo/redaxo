<?php

/**
 * Represents a null addon.
 */
class rex_null_addon extends rex_null_package implements rex_addon_interface
{
    public function getType()
    {
        return 'addon';
    }
}
