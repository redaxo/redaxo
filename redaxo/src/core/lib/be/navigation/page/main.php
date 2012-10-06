<?php

class rex_be_page_main implements rex_be_page_container
{
  private
    $block,
    $page,
    $prio = 0;

  public function __construct($block, rex_be_page $page)
  {
    if (!is_string($block)) {
      throw new rex_exception('Expecting $block to be a string, ' . gettype($block) . 'given!');
    }

    $this->block = $block;
    $this->page = $page;
  }

  public function setBlock($block)
  {
    $this->block = $block;
  }

  public function getBlock()
  {
    return $this->block;
  }

  public function getPage()
  {
    return $this->page;
  }

  public function setPrio($prio)
  {
    $this->prio = $prio;
  }

  public function getPrio()
  {
    return $this->prio;
  }

  public function _set($key, $value)
  {
    if (!is_string($key))
      return;

    // check current object for a possible setter
    $setter = array($this, 'set' . ucfirst($key));
    if (is_callable($setter)) {
      call_user_func($setter, $value);
    } else {
      // no setter found, delegate to page object
      $setter = array($this->page, 'set' . ucfirst($key));
      if (is_callable($setter)) {
        call_user_func($setter, $value);
      }
    }
  }

  /*
   * Static Method: Returns True when the given be_main_page is valid
   */
  static public function isValid($be_main_page)
  {
    return is_object($be_main_page) && is_a($be_main_page, __CLASS__);
  }
}
