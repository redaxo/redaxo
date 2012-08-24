<?php

/**
 * @see rex_media
 *
 * @deprecated 5.0
 */
class OOMedia extends rex_media
{
  /**
   * @see rex_url::media()
   *
   * @deprecated 5.0
   */
  public function getPath()
  {
    return rex_url::media();
  }

  /**
   * @see rex_media::getUrl()
   *
   * @deprecated 5.0
   */
  public function getFullPath()
  {
    return $this->getUrl();
  }

  /**
   * @see rex_file::extension()
   *
   * @deprecated 5.0
   */
  static public function _getExtension($filename)
  {
    return substr(strrchr($filename, '.'), 1);
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

  /**
   * instead: "$media instanceof rex_media"
   *
   * @deprecated 5.0
   */
  static public function isValid($media)
  {
    return $media instanceof parent;
  }
}
