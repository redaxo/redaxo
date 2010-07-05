<?php

class rex_input_mediabutton extends rex_input
{
  var $buttonId;
  var $categoryId;
  var $args = array();
  
  function rex_input_mediabutton()
  {
    parent::rex_input();
    $this->buttonId = '';
  }
  
  function setButtonId($buttonId)
  {
    $this->buttonId = $buttonId;
    $this->setAttribute('id', 'REX_MEDIA_'. $buttonId);
  }
  
  function setCategoryId($categoryId)
  {
    $this->categoryId = $categoryId;
  }
  
  function setTypes($types)
  {
    $this->args['types'] = $types;
  }
  
  function setPreview($preview = true)
  {
    $this->args['preview'] = $preview;
  }
  
  function getHtml()
  {
    $buttonId = $this->buttonId;
    $categoryId = $this->categoryId;
    $value = htmlspecialchars($this->value);
    $name = $this->attributes['name'];
    $args = $this->args;
    
    $field = rex_var_media::getMediaButton($buttonId, $categoryId, $args);
    $field = str_replace('REX_MEDIA['. $buttonId .']', $value, $field);
    $field = str_replace('MEDIA['. $buttonId .']', $name, $field);
    
    return $field;
  }
}