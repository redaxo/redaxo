<?php

class rex_input_text extends rex_input
{
  function rex_input_text()
  {
    parent::rex_input();
    $this->setAttribute('class', 'rex-form-text');
    $this->setAttribute('type', 'text');
  }
  
  function getHtml()
  {
    $value = htmlspecialchars($this->value);
    return '<input'. $this->getAttributeString() .' value="'. $value .'" />';
  }
}