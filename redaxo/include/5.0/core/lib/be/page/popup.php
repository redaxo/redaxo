<?php

class rex_be_page_popup extends rex_be_page
{
  function rex_be_page_popup($title, $onclick = '', $activateCondition = array())
  {
    parent::rex_be_page($title, $activateCondition);
    
    $this->setHasNavigation(false);
    $this->onclick = $onclick;
    $this->addItemClass('rex-popup');
    $this->addLinkClass('rex-popup');
    $this->setLinkAttr('onclick', $onclick);
  }
}