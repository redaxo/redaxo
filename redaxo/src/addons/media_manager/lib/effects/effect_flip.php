<?php

/**
 * Spiegel ein Bild.
 *
 * @package redaxo\media-manager
 */
class rex_effect_flip extends rex_effect_abstract
{
    private $options;

    public function __construct()
    {
        $this->options = [
            'X', 'Y', 'XY',
        ];
    }

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
        return rex_i18n::msg('media_manager_effect_flip');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_flip_direction'),
                'name' => 'flip',
                'type' => 'select',
                'options' => $this->options,
                'default' => 'X',
            ],
        ];
    }
}
