<?php

/**
 * Represents a null addon.
 *
 * @author gharlan
 *
 * @package redaxo\core\packages
 */
class rex_null_addon extends rex_null_package implements rex_addon_interface
{
    public function getType()
    {
        return 'addon';
    }
}
