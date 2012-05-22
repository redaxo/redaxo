<?php

class rex_input_mediabutton extends rex_input
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
    $this->setAttribute('id', 'REX_MEDIA_' . $buttonId);
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

    $field = rex_var_media::getMediaButton($buttonId, $categoryId, $args);
    $field = str_replace('REX_MEDIA[' . $buttonId . ']', $value, $field);
    $field = str_replace('MEDIA[' . $buttonId . ']', $name, $field);

    return $field;
  }
}
