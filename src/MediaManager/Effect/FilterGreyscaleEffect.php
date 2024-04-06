<?php

namespace Redaxo\Core\MediaManager\Effect;

use Redaxo\Core\Translation\I18n;

use const IMG_FILTER_GRAYSCALE;

class FilterGreyscaleEffect extends AbstractEffect
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
        return I18n::msg('media_manager_effect_greyscale');
    }

    public function getParams()
    {
        return [
        ];
    }
}
