<?php

abstract class rex_effect_abstract
{
  protected
    $image, // rex_image
    $params = array(); // effekt parameter

  public function setImage(rex_image $image)
  {
  	$this->image = $image;
  }

  public function setParams(array $params)
  {
  	$this->params = $params;
  }

  /**
   * exectute effect on $this->img
   */
  abstract public function execute();

  /**
   * returns an array of parameters which are required for the effect
   */
  public function getParams()
  {
    // NOOP
  }
}