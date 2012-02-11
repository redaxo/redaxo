<?php

class rex_input_textarea extends rex_input
{
  public function __construct()
  {
    parent::__construct();
    $this->setAttribute('class', 'rex-form-textarea');
    $this->setAttribute('cols', '50');
    $this->setAttribute('rows', '6');
  }

  public function getHtml()
  {
    $value = htmlspecialchars($this->value);
    return '<textarea'. $this->getAttributeString() .'>'. $value .'</textarea>';
  }
}
