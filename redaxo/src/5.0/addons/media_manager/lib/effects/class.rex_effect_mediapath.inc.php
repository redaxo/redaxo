<?php

class rex_effect_mediapath extends rex_effect_abstract
{

  public function __construct()
  {
  }

  public function execute()
  {
    if($this->params['mediapath'] != "")
    {
      $media_path = rex_path::frontend($this->params['mediapath'],rex_path::ABSOLUTE)."/".rex_media_manager::getMediaFile();
      $this->media->setMediapath($media_path);    
    }
  }
  
  public function getParams()
  {
    return array(
      array(
        'label'=>rex_i18n::msg('media_manager_effect_mediapath'),
        'name' => 'mediapath',
        'type' => 'string',
      ),
    );
  }
}