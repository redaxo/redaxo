<?php

class rex_input_linklistbutton extends rex_input
{
  var $buttonId;
  var $categoryId;
  
  function rex_input_linklistbutton()
  {
    parent::rex_input();
    $this->buttonId = '';
    $this->categoryId = '';
  }
  
  function setButtonId($buttonId)
  {
    $this->buttonId = $buttonId;
    $this->setAttribute('id', 'REX_LINKLIST_'. $buttonId);
  }
  
  function setCategoryId($categoryId)
  {
    $this->categoryId = $categoryId;
  }
  
  function getHtml()
  {
    $buttonId = $this->buttonId;
    $category = $this->categoryId;
    $value = htmlspecialchars($this->value);
    $name = $this->attributes['name'];
    
    $field = rex_var_link::getLinklistButton($buttonId, $value, $category);
    $field = str_replace('LINKLIST['. $buttonId .']', $name, $field);
    
    return $field;
  }
}