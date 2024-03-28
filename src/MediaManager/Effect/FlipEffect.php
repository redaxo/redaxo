<?php

namespace Redaxo\Core\MediaManager\Effect;

use Redaxo\Core\Translation\I18n;

use const IMG_FLIP_BOTH;
use const IMG_FLIP_HORIZONTAL;
use const IMG_FLIP_VERTICAL;

/**
 * Spiegel ein Bild.
 */
class FlipEffect extends AbstractEffect
{
    private const OPTIONS = ['X', 'Y', 'XY'];

    public function execute()
    {
        $this->media->asImage();
        $gdimage = $this->media->getImage();

        // transparenz erhalten (für GIF, PNG & WebP)
        $this->keepTransparent($gdimage);

        // --------------- Flip X
        if ('X' == $this->params['flip']) {
            imageflip($gdimage, IMG_FLIP_HORIZONTAL);
            $this->media->setImage($gdimage);
        }

        // --------------- Flip Y
        if ('Y' == $this->params['flip']) {
            imageflip($gdimage, IMG_FLIP_VERTICAL);
            $this->media->setImage($gdimage);
        }

        // --------------- Flip X and Y
        if ('XY' == $this->params['flip']) {
            imageflip($gdimage, IMG_FLIP_BOTH);
            $this->media->setImage($gdimage);
        }
    }

    public function getName()
    {
        return I18n::msg('media_manager_effect_flip');
    }

    public function getParams()
    {
        return [
            [
                'label' => I18n::msg('media_manager_effect_flip_direction'),
                'name' => 'flip',
                'type' => 'select',
                'options' => self::OPTIONS,
                'default' => 'X',
            ],
        ];
    }
}
