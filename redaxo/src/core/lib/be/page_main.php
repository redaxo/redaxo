<?php

class rex_be_page_main extends rex_be_page
{
  private
    $block,
    $prio = 0;

  public function __construct($block, $key, $title)
  {
    if (!is_string($block)) {
      throw new rex_exception('Expecting $block to be a string, ' . gettype($block) . 'given!');
    }
    $this->block = $block;

    parent::__construct($key, $title);
  }

  public function setBlock($block)
  {
    $this->block = $block;
  }

  public function getBlock()
  {
    return $this->block;
  }

  public function setPrio($prio)
  {
    $this->prio = $prio;
  }

  public function getPrio()
  {
    return $this->prio;
  }
}
