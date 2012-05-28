<?php

/**
 * Represents a null plugin
 *
 * @author gharlan
 */
class rex_null_plugin extends rex_null_package implements rex_plugin_interface
{
  /* (non-PHPdoc)
   * @see rex_package_interface::getType()
   */
  public function getType()
  {
    return 'plugin';
  }
}
