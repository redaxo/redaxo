<?php

/**
 * @see rex_media_category
 *
 * @deprecated 5.0
 */
class OOMediaCategory extends rex_media_category
{
  /**
   * instead: "$mediaCat instanceof rex_media_category"
   *
   * @deprecated 5.0
   */
  static public function isValid($mediaCat)
  {
    return $mediaCat instanceof parent;
  }
}
