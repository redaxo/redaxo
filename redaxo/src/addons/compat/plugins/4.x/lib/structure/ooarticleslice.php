<?php

/**
 * @see rex_article_slice
 *
 * @deprecated 5.0
 */
class OOArticleSlice extends rex_article_slice
{
  /**
   * @see getMedia()
   * @deprecated 4.1 - 05.03.2008
   */
  public function getFile($index)
  {
    return $this->getMedia($index);
  }

  /**
   * @see getMediaUrl()
   * @deprecated 4.1 - 05.03.2008
   */
  public function getFileUrl($index)
  {
    return $this->getMediaUrl($index);
  }

  /**
   * @see getMediaList
   * @deprecated 4.1 - 05.03.2008
   */
  public function getFileList($index)
  {
    return $this->getMediaList($index);
  }

  /**
   * @see getModuleId()
   * @deprecated 4.1 - 05.03.2008
   */
  public function getModulId()
  {
    return $this->getModuleId();
  }

  /**
   * @see getModuleId()
   * @deprecated 4.1 - 05.03.2008
   */
  public function getModulTyp()
  {
    return $this->getModuleId();
  }

  /**
   * @see getPreviousSlice()
   * @deprecated 4.1 - 07.03.2008
   */
  public function getPrevSlice()
  {
    return $this->getPreviousSlice();
  }
}
