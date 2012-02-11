<?php

/**
 * @see rex_ooCategory
 *
 * @deprecated 5.0
 */
class OOCategory extends rex_ooCategory
{
  /**
   * @see rex_ooArticle::getValue()
   *
   * @deprecated 4.0 17.09.2007
   */
  public function getFile()
  {
    return $this->getValue('art_file');
  }

  /**
   * @see rex_ooArticle::getValue()
   *
   * @deprecated 4.0 17.09.2007
   */
  public function getFileMedia()
  {
    return rex_ooMedia :: getMediaByFileName($this->getValue('art_file'));
  }

  /**
   * @see rex_ooArticle::getValue()
   *
   * @deprecated 4.0 17.09.2007
   */
  public function getDescription()
  {
    return $this->getValue('art_description');
  }

  /**
   * @see rex_ooArticle::getValue()
   *
   * @deprecated 4.0 17.09.2007
   */
  public function getTypeId()
  {
    return $this->getValue('art_type_id');
  }
}
