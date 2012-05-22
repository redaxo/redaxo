<?php

/**
 * @see rex_i18n
 *
 * @deprecated 5.0
 */
class i18n extends rex_i18n
{
  /**
   * @see rex_i18n::setLocale()
   *
   * @deprecated 5.0
   */
  public function __construct($locale = "de_de", $searchpath = null, $setLocale = false)
  {
    global $REX;

    parent::setLocale($locale, $setLocale);
    if($searchpath !== null)
    {
      parent::addDirectory($searchpath);
    }

    $REX['LOCALES'] = rex_i18n::getLocales();
    $REX['LANGUAGES'] = $REX['LOCALES'];
  }

  /**
   * @see rex_i18n::addDirectory()
   *
   * @deprecated 5.0
   */
  public function appendFile($searchPath)
  {
    parent::addDirectory($searchPath);
  }
}
