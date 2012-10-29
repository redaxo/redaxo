<?php

class rex_be_page_popup extends rex_be_page
{
  public function __construct($key, $title, $onclick = '')
  {
    parent::__construct($key, $title);

    $this->setHasNavigation(false);
    $this->onclick = $onclick;
    $this->addItemClass('rex-popup');
    $this->addLinkClass('rex-popup');
    $this->setLinkAttr('onclick', $onclick);
  }
}
