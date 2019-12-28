<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_filter_greyscale extends rex_effect_abstract
{
    public function execute()
    {
        $this->media->asImage();
        $img = $this->media->getImage();

        imagefilter($img, IMG_FILTER_GRAYSCALE);

        $this->media->setImage($img);
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_greyscale');
    }

    public function getParams()
    {
        return [
        ];
    }
}
