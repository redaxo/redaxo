<?php

/**
 * @see rex_select
 *
 * @deprecated 4.0
 */
class select extends rex_select
{
  /**
   * @see rex_select::setMultiple()
   *
   * @deprecated 4.0
   */
  public function multiple($mul)
  {
    $this->setMultiple($mul);
  }

  /**
   * @see rex_select::setAttribute()
   *
   * @deprecated 4.0
   */
  public function set_selectextra($extra)
  {
    foreach(rex_var::splitString($extra) as $name => $value)
    {
      $this->setAttribute($name, $value);
    }
  }

  /**
   * @see rex_select::get()
   *
   * @deprecated 4.0
   */
  public function out()
  {
    return $this->get();
  }

  /**
   * @see rex_select::setName()
   *
   * @deprecated 4.0
   */
  public function set_name($name)
  {
    $this->setName($name);
  }

  /**
   * @see rex_select::setId()
   *
   * @deprecated 4.0
   */
  public function set_id($id)
  {
    $this->setId($id);
  }

  /**
   * @see rex_select::setSize()
   *
   * @deprecated 4.0
   */
  public function set_size($size)
  {
    $this->setSize($size);
  }

  /**
   * @see rex_select::setSelected()
   *
   * @deprecated 4.0
   */
  public function set_selected($selected)
  {
    $this->setSelected($selected);
  }

  /**
   * @see rex_select::resetSelected()
   *
   * @deprecated 4.0
   */
  public function reset_selected()
  {
    $this->resetSelected();
  }

  /**
   * @see rex_select::setStyle()
   *
   * @deprecated 4.0
   */
  public function set_style($style)
  {
    $this->setStyle($style);
  }

  /**
   * @see rex_select::addOption()
   *
   * @deprecated 4.0
   */
  public function add_option($name, $value, $id = 0, $re_id = 0)
  {
    $this->addOption($name, $value, $id, $re_id);
  }
}
