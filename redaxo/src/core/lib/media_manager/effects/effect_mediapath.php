<?php

use Redaxo\Core\Translation\I18n;

class rex_effect_mediapath extends rex_effect_abstract
{
    public function __construct() {}

    public function execute()
    {
        if ('' != $this->params['mediapath']) {
            $mediaPath = rex_path::frontend($this->params['mediapath'] . '/' . $this->media->getMediaFilename());
            $this->media->setMediaPath($mediaPath);
        }
    }

    public function getName()
    {
        return I18n::msg('media_manager_effect_mediapath');
    }

    public function getParams()
    {
        return [
            [
                'label' => I18n::msg('media_manager_effect_mediapath_path'),
                'name' => 'mediapath',
                'type' => 'string',
                'notice' => I18n::msg('media_manager_effect_mediapath_path_notice'),
            ],
        ];
    }
}
