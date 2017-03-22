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
            'X', 'Y', 'XY'
        ];
    }

    public function execute()
    {
        $this->media->asImage();
        $gdimage = $this->media->getImage();

        // transparenz erhalten (für GIF, PNG & WebP)
        $this->keepTransparent($output);

        // --------------- Flip X
        if ($this->params['flip'] == 'X') {
            imageflip($gdimage, IMG_FLIP_HORIZONTAL);
            $this->media->setImage($gdimage);
        }

        // --------------- Flip Y
        if ($this->params['flip'] == 'Y') {
            imageflip($gdimage, IMG_FLIP_VERTICAL);
            $this->media->setImage($gdimage);
        }

        // --------------- Flip X and Y
        if ($this->params['flip'] == 'XY') {
            imageflip($gdimage, IMG_FLIP_BOTH);
            $this->media->setImage($gdimage);
        }
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_flip'),
                'name' => 'flip',
                'type' => 'select',
                'options' => $this->options,
                'default' => 'X',
            ],
        ];
    }
}
