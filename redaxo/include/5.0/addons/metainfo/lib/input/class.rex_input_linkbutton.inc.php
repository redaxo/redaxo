<?php

class rex_input_linkbutton extends rex_input
{
  var $buttonId;
  var $categoryId;
  
  function rex_input_linkbutton()
  {
    parent::rex_input();
    $this->buttonId = '';
    $this->categoryId = '';
  }
  
  function setButtonId($buttonId)
  {
    $this->buttonId = $buttonId;
    $this->setAttribute('id', 'LINK_'. $buttonId);
  }
  
  function setCategoryId($categoryId)
  {
    $this->categoryId = $categoryId;
  }
  
  function getHtml()
  {
    $buttonId = $this->buttonId;
    $categoryId = $this->categoryId;
    $value = htmlspecialchars($this->value);
    $name = $this->attributes['name'];
    
    $field = rex_var_link::_getLinkButton($name, $buttonId, $value, $categoryId);
    
    return $field;
  }
}