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
}
