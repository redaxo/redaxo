<?php

/**
 * @see rex_ooMedia
 *
 * @deprecated 5.0
 */
class OOMedia extends rex_ooMedia
{
	/**
   * @see rex_file::extension()
   *
   * @deprecated 5.0
   */
  static public function _getExtension($filename)
  {
    return substr(strrchr($filename, "."), 1);
  }

	/**
   * @see rex_file::formattedSize()
   *
   * @deprecated 5.0
   */
  static public function _getFormattedSize($size)
  {
    return rex_file::formattedSize($size);
  }
}