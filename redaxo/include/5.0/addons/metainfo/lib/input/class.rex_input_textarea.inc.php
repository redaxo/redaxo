<?php

class rex_input_textarea extends rex_input
{
  function rex_input_textarea()
  {
    parent::rex_input();
    $this->setAttribute('class', 'rex-form-textarea');
    $this->setAttribute('cols', '50');
    $this->setAttribute('rows', '6');
  }
  
  function getHtml()
  {
    $value = htmlspecialchars($this->value);
    return '<textarea'. $this->getAttributeString() .'>'. $value .'</textarea>';
  }
}