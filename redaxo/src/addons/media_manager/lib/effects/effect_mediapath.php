<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_mediapath extends rex_effect_abstract
{
    public function __construct()
    {
    }

    public function execute()
    {
        if ($this->params['mediapath'] != '') {
            $media_path = rex_path::frontend($this->params['mediapath'] . '/' . rex_media_manager::getMediaFile());
            $this->media->setMediaPath($media_path);
        }
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_mediapath');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_mediapath_path'),
                'name' => 'mediapath',
                'type' => 'string',
            ],
        ];
    }
}
