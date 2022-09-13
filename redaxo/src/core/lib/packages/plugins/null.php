<?php

/**
 * Represents a null plugin.
 *
 * @author gharlan
 *
 * @package redaxo\core\packages
 */
class rex_null_plugin extends rex_null_package implements rex_plugin_interface
{
    public function getType()
    {
        return 'plugin';
    }
}
