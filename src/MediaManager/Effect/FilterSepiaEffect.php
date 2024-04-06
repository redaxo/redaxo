<?php

namespace Redaxo\Core\MediaManager\Effect;

use Redaxo\Core\Translation\I18n;

use const IMG_FILTER_BRIGHTNESS;
use const IMG_FILTER_COLORIZE;
use const IMG_FILTER_GRAYSCALE;

class FilterSepiaEffect extends AbstractEffect
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
        return I18n::msg('media_manager_effect_sepia');
    }

    public function getParams()
    {
        return [
        ];
    }
}
