<?php

/**
 * Sprachobjekt zur Internationalisierung (I18N)
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class i18n extends rex_i18n
{
  /*
   * Constructor
   * the locale must of the common form, eg. de_de, en_us or just plain en, de.
   * the searchpath is where the language files are located
   */
  public function __construct($locale = "de_de", $searchpath)
  {
    parent::__construct($locale, $searchpath);
  }

}
