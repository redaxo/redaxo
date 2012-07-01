<?php

abstract class rex_effect_abstract
{
  public
    $media, // rex_managed_media
    $params = array(); // effekt parameter

  public function setMedia(rex_managed_media $media)
  {
    $this->media = $media;
  }

  public function setParams(array $params)
  {
    $this->params = $params;
  }

  abstract public function execute();

  public function getParams()
  {
    // NOOP
  }
}
