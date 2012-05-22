<?php

class rex_input_medialistbutton extends rex_input
{
  private
    $buttonId,
    $categoryId,
    $args = array();

  public function __construct()
  {
    parent::__construct();
    $this->buttonId = '';
  }

  public function setButtonId($buttonId)
  {
    $this->buttonId = $buttonId;
    $this->setAttribute('id', 'REX_MEDIALIST_' . $buttonId);
  }

  public function setCategoryId($categoryId)
  {
    $this->categoryId = $categoryId;
  }

  public function setTypes($types)
  {
    $this->args['types'] = $types;
  }

  public function setPreview($preview = true)
  {
    $this->args['preview'] = $preview;
  }

  public function getHtml()
  {
    $buttonId = $this->buttonId;
    $categoryId = $this->categoryId;
    $value = htmlspecialchars($this->value);
    $name = $this->attributes['name'];
    $args = $this->args;

    $field = rex_var_media::getMediaListButton($buttonId, $value, $categoryId, $args);
    $field = str_replace('MEDIALIST[' . $buttonId . ']', $name, $field);

    return $field;
  }
}
