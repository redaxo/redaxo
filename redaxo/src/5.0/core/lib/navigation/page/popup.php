<?php

class rex_be_page_popup extends rex_be_page
{
  public function __construct($title, $onclick = '', array $activateCondition = array())
  {
    parent::__construct($title, $activateCondition);

    $this->setHasNavigation(false);
    $this->onclick = $onclick;
    $this->addItemClass('rex-popup');
    $this->addLinkClass('rex-popup');
    $this->setLinkAttr('onclick', $onclick);
  }

  /*
   * Static Method: Returns True when the given be_page is valid
   */
  static public function isValid($be_page)
  {
    return is_object($be_page) && is_a($be_page, 'rex_be_page_popup');
  }
}