<?php

/**
 * Dreht ein Bild.
 *
 * @package redaxo\media-manager
 */
class rex_effect_rotate extends rex_effect_abstract
{
    private $options;

    public function __construct()
    {
        $this->options = [
            '0', '90', '180', '270',
        ];
    }

    public function execute()
    {
        $this->media->asImage();
        $gdimage = $this->media->getImage();
        $gdimage = imagerotate($gdimage, $this->params['rotate'], 0);
        $this->keepTransparent($gdimage);
        $this->media->setImage($gdimage);
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_rotate');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_rotate_degree'),
                'name' => 'rotate',
                'type' => 'select',
                'options' => $this->options,
                'default' => '0',
            ],
        ];
    }
}
