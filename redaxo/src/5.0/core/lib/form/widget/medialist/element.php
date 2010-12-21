<?php

class rex_form_widget_medialist_element extends rex_form_element
{
  private
    $category_id = 0,
    $args = array();

  // 1. Parameter nicht genutzt, muss aber hier stehen,
  // wg einheitlicher Konstrukturparameter
  function __construct($tag = '', &$table, array $attributes = array())
  {
    parent::__construct('', $table, $attributes);
  }

  public function setCategoryId($category_id)
  {
    $this->category_id = $category_id;
  }

  public function setTypes($types)
  {
    $this->args['types'] = $types;
  }

  public function setPreview($preview = true)
  {
    $this->args['preview'] = $preview;
  }

  public function formatElement()
  {
    static $widget_counter = 1;

    $html = rex_var_media::getMediaListButton($widget_counter, $this->getValue(), $this->category_id, $this->args);
    $html = str_replace('MEDIALIST['. $widget_counter .']', $this->getAttribute('name'), $html);

    $widget_counter++;
    return $html;
  }
}