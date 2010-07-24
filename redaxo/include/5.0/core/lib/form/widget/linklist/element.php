<?php

class rex_form_widget_linklist_element extends rex_form_element
{
  var $category_id = 0;

  // 1. Parameter nicht genutzt, muss aber hier stehen,
  // wg einheitlicher Konstrukturparameter
  function rex_form_widget_linklist_element($tag = '', &$table, $attributes = array())
  {
    parent::rex_form_element('', $table, $attributes);
  }

  function setCategoryId($category_id)
  {
    $this->category_id = $category_id;
  }

  function formatElement()
  {
    static $widget_counter = 1;

    $html = rex_var_link::getLinkListButton($widget_counter, $this->getValue(), $this->category_id);
    $html = str_replace('LINKLIST['. $widget_counter .']', $this->getAttribute('name'), $html);

    $widget_counter++;
    return $html;
  }
}
