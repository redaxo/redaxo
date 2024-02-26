<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_filter_sepia extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->asImage();
        $img = $this->media->getImage();
        imagefilter($img, IMG_FILTER_GRAYSCALE);
        imagefilter($img, IMG_FILTER_BRIGHTNESS, -30);
        imagefilter($img, IMG_FILTER_COLORIZE, 90, 55, 30);
        $this->keepTransparent($img);
        $this->media->setImage($img);
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_sepia');
    }

    public function getParams()
    {
        return [
        ];
    }
}
